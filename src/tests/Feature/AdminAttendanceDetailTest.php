<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected User $user;
    protected Attendance $attendance;
    protected BreakTime $breakTime;

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

        $this->admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->user = User::factory()->create([
            'name' => 'テスト太郎',
            'status_id' => 2,
            'email_verified_at' => now(),
        ]);

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-05-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->breakTime = BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
    }

     /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get("/attendance/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('2025年');
        $response->assertSee('5月1日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合_エラー表示される()
    {
        $response = $this->withoutMiddleware()
                        ->actingAs($this->admin, 'admin')
                        ->from(route('attendance.show', ['id' => $this->attendance->id]))
                        ->put(route('admin.attendance.update', ['attendance' => $this->attendance->id]), [
                            'clock_in' => '19:00',  // 不正な出勤時間
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
    public function 休憩開始時間が退勤時間より後の場合_エラー表示される()
    {
        $response = $this->withoutMiddleware()
                        ->actingAs($this->admin, 'admin')
                        ->from(route('attendance.show', ['id' => $this->attendance->id]))
                        ->put(route('admin.attendance.update', ['attendance' => $this->attendance->id]), [
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
    public function 休憩終了時間が退勤時間より後の場合_エラー表示される()
    {
        $response = $this->withoutMiddleware()
                        ->actingAs($this->admin, 'admin')
                        ->from(route('attendance.show', ['id' => $this->attendance->id]))
                        ->put(route('admin.attendance.update', ['attendance' => $this->attendance->id]), [
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
    public function 備考が空の場合_エラー表示される()
    {
        $response = $this->withoutMiddleware()
                        ->actingAs($this->admin, 'admin')
                        ->from(route('attendance.show', ['id' => $this->attendance->id]))
                        ->put(route('admin.attendance.update', ['attendance' => $this->attendance->id]), [
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
}
