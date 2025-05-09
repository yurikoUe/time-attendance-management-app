<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceEditValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('user_statuses')->insert([
            ['id' => 1, 'name' => '勤務外'],
            ['id' => 2, 'name' => '出勤中'],
            ['id' => 3, 'name' => '休憩中'],
            ['id' => 4, 'name' => '退勤済'],
        ]);

        DB::table('attendance_request_statuses')->insert([
            ['id' => 1, 'name' => '承認待ち'],
            ['id' => 2, 'name' => '承認済み'],
        ]);

        $this->user = User::factory()->create([
            'status_id' => 2,
            'email_verified_at' => now(),
        ]);

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
    }

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合、エラーエッセージが表示される()
    {
        $response = $this->withoutMiddleware()
                        ->actingAs($this->user)
                        ->from(route('attendance.show', ['id' => $this->attendance->id]))
                        ->post(route('attendance-request.store', ['id' => $this->attendance->id]), [
                            'clock_in' => '19:00',
                            'clock_out' => '18:00',
                            'breaks' => [
                                ['break_start' => '12:00', 'break_end' => '13:00'],
                            ],
                            'request_reason' => 'テスト備考',
                        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);

    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合、エラーエッセージが表示される()
    {
        $response = $this->withoutMiddleware()
                        ->actingAs($this->user)
                        ->from(route('attendance.show', ['id' => $this->attendance->id]))
                        ->post(route('attendance-request.store', ['id' => $this->attendance->id]), [
                            'clock_in' => '09:00',
                            'clock_out' => '18:00',
                            'breaks' => [
                                ['break_start' => '19:00', 'break_end' => '20:00'],
                            ],
                            'request_reason' => 'テスト備考',
                        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));

        $response->assertSessionHasErrors([
            'breaks.0.break_end' => '休憩時間が勤務時間外です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合、エラーエッセージが表示される()
    {
        $response = $this->withoutMiddleware()
                        ->actingAs($this->user)
                        ->from(route('attendance.show', ['id' => $this->attendance->id]))
                        ->post(route('attendance-request.store', ['id' => $this->attendance->id]), [
                            'clock_in' => '09:00',
                            'clock_out' => '18:00',
                            'breaks' => [
                                ['break_start' => '12:00', 'break_end' => '19:00'],
                            ],
                            'request_reason' => 'テスト備考',
                        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));

        $response->assertSessionHasErrors([
            'breaks.0.break_end' => '休憩時間が勤務時間外です',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合、エラーエッセージが表示される()
    {
        $response = $this->withoutMiddleware()
                        ->actingAs($this->user)
                        ->from(route('attendance.show', ['id' => $this->attendance->id]))
                        ->post(route('attendance-request.store', ['id' => $this->attendance->id]), [
                            'clock_in' => '09:00',
                            'clock_out' => '18:00',
                            'breaks' => [
                                ['break_start' => '12:00', 'break_end' => '13:00'],
                            ],
                            'request_reason' => '',
                        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));
        $response->assertSessionHasErrors([
            'request_reason' => '備考を記入してください',
        ]);
    }

     /** @test */
    public function 修正申請処理が実行される()
    {
        $response = $this->withoutMiddleware()
                        ->actingAs($this->user)
                        ->from(route('attendance.show', ['id' => $this->attendance->id]))
                        ->post(route('attendance-request.store', ['id' => $this->attendance->id]), [
                            'clock_in' => '10:00',
                            'clock_out' => '16:00',
                            'breaks' => [
                                ['break_start' => '11:00', 'break_end' => '12:00'],
                            ],
                            'request_reason' => 'テスト備考',
                        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));
        $response->assertSessionHas('success', '申請が完了しました');
    }

    /** @test */
    public function 承認待ちにログインユーザーが行った申請が全て表示されている()
    {
        // ログインしたユーザーで申請を作成
        $response = $this->withoutMiddleware()
                        ->actingAs($this->user)
                        ->from(route('attendance.show', ['id' => $this->attendance->id]))
                        ->post(route('attendance-request.store', ['id' => $this->attendance->id]), [
                            'clock_in' => '10:00',
                            'clock_out' => '16:00',
                            'breaks' => [
                                ['break_start' => '11:00', 'break_end' => '12:00'],
                            ],
                            'request_reason' => 'テスト備考',
                        ]);
        
        $response->assertStatus(302);
        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));

        // 申請一覧画面に遷移し、承認待ち申請が表示されているか確認
        $response = $this->actingAs($this->user)->get(route('attendance-request.index'));
        $response->assertStatus(200);
        $response->assertSee('承認待ち');  // 申請のステータス「承認待ち」が表示される
    }

    /** @test */
    public function 承認済みに管理者が承認した修正申請が全て表示されている()
    {
        // 管理者でログイン
        $admin = Admin::factory()->create();

        // ユーザーの勤怠申請を修正
        $response = $this->withoutMiddleware()
                        ->actingAs($this->user)
                        ->from(route('attendance.show', ['id' => $this->attendance->id]))
                        ->post(route('attendance-request.store', ['id' => $this->attendance->id]), [
                            'clock_in' => '10:00',
                            'clock_out' => '16:00',
                            'breaks' => [
                                ['break_start' => '11:00', 'break_end' => '12:00'],
                            ],
                            'request_reason' => 'テスト備考',
                        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('attendance.show', ['id' => $this->attendance->id]));

        // 管理者で承認
        $attendanceRequest = DB::table('attendance_requests')->first();
        $response = $this->actingAs($admin)->post(route('attendance-request.index'));
        $response->assertStatus(302);
        $response->assertRedirect(route('attendance-request.index'));

        // 承認済み画面で申請が表示されることを確認
        $response = $this->actingAs($admin)->get(route('attendance-request.index'));
        $response->assertStatus(200);
        $response->assertSee('承認済み');  // 申請のステータス「承認済み」が表示される
    }



}
