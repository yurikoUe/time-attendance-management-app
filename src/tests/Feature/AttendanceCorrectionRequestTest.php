<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceRequestStatus;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestDetail;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;
    protected Admin $admin;
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

        $this->admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // 一般ユーザー作成
        $this->user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'status_id' => 2,
            'email_verified_at' => now(),
        ]);

        // 勤怠データを生成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
    }

    /** @test */
    public function 承認待ちの修正申請が全て表示されている()
    {
        $this->actingAs($this->admin, 'admin');

        AttendanceRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'status_id' => 1,
            'request_reason' => '出勤時間修正',
        ]);

        $response = $this->get('/stamp_correction_request/list?status=waiting');
        $response->assertStatus(200);
        $response->assertSee('出勤時間修正');
    }

    /** @test */
    public function 承認済みの修正申請が全て表示されている()
    {
        $this->actingAs($this->admin, 'admin');

        AttendanceRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'status_id' => 2,
            'request_reason' => '退勤時間修正',
        ]);

        $response = $this->get('/stamp_correction_request/list?status=approved'); 
        $response->assertStatus(200);
        $response->assertSee('退勤時間修正');
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示されている()
    {
        $this->actingAs($this->admin, 'admin');

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'status_id' => 1,
            'request_reason' => '出勤時間ミス',
        ]);

        $requestDetail = AttendanceRequestDetail::factory()->create([
            'attendance_request_id' => $request->id,
            'after_clock_in' => '10:00:00',
            'after_clock_out' => '17:00:00',
        ]);

        $response = $this->get("/stamp_correction_request/approve/{$requestDetail->attendance_request_id}");

        $response->assertStatus(200);
        $response->assertSee('出勤時間ミス');
        $response->assertSee('10:00');
        $response->assertSee('17:00');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $this->actingAs($this->admin, 'admin');

        // 承認待ちの修正申請を作成
        $attendanceRequest = AttendanceRequest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'status_id' => 1,  // 承認待ち
            'request_reason' => '出勤時間ミス',
        ]);

        // 修正内容
        $requestDetail = AttendanceRequestDetail::factory()->create([
            'attendance_request_id' => $attendanceRequest->id,
            'after_clock_in' => '10:00:00',
            'after_clock_out' => '17:00:00',
        ]);

        $csrfToken = csrf_token();

        // 承認処理を実行
        $response = $this->withoutMiddleware(['auth:admin', 'role:admin', 'csrf'])
                        ->actingAs($this->admin, 'admin')
                        ->post("/stamp_correction_request/approve/{$attendanceRequest->id}", [
            '_token' => $csrfToken,
        ]);
        // 承認後のステータスが「承認済み」になっているか確認
        $attendanceRequest->refresh();

        $this->assertEquals(2, $attendanceRequest->status_id); // 承認済みのID (2) を確認

        // 勤怠情報の修正が反映されているか確認
        $attendance = Attendance::find($attendanceRequest->attendance_id);
        $this->assertEquals('10:00:00', $attendance->clock_in); // 修正された出勤時間
        $this->assertEquals('17:00:00', $attendance->clock_out); // 修正された退勤時間

        $response->assertStatus(302); // リダイレクトを確認
        $response->assertRedirect('/stamp_correction_request/list'); // 修正申請一覧にリダイレクトされる
    }
}
