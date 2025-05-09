<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('user_statuses')->insert([
            ['id' => 1, 'name' => '勤務外'],
            ['id' => 2, 'name' => '出勤中'],
            ['id' => 3, 'name' => '休憩中'],
            ['id' => 4, 'name' => '退勤済'],
        ]);
    }

    /** @test */
    public function 退勤ボタンが表示されて処理後にステータスが退勤済になる()
    {
        // 出勤中のユーザーを作成してログイン
        $user = User::factory()->create([
            'status_id' => 2, // 出勤中
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        // 出勤レコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(5),
        ]);

        // 勤怠画面を開く
        $response = $this->get('/attendance');

        // 退勤ボタンが表示されていることを確認
        $response->assertSee('退勤');

        // 退勤処理を実行
        $response = $this->post('/attendance/end', [
            '_token' => csrf_token(),
        ]);

        // ステータス更新を確認
        $user->refresh();
        $this->assertEquals(4, $user->status_id); // 退勤済

        // 勤怠画面を再取得してステータス表示を確認
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が管理画面で確認できる()
    {
        // 管理者を作成してログイン
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // ユーザー作成
        $user = User::factory()->create([
            'status_id' => 4, // 退勤済
            'email_verified_at' => now(),
            'name' => 'テスト太郎',
        ]);

        // 出勤〜退勤済みレコードを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8)->setSeconds(0),
            'clock_out' => now()->setSeconds(0),
        ]);

        // 管理画面の勤怠一覧にアクセス
        $response = $this->get('/admin/attendance/list?date=' . now()->toDateString());

        // ユーザー名と退勤時刻（H:i形式）が表示されていることを確認
        $response->assertSee($user->name);
        $response->assertSee($attendance->clock_out->format('H:i'));
    }


}