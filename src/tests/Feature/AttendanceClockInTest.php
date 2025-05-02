<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;

class AttendanceClockInTest extends TestCase
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
    public function 出勤ボタンが表示されて出勤処理後に勤務中になる()
    {
        // テスト用ユーザー作成＆ログイン
        $user = User::factory()->create([
            'status_id' => 1, //勤務外
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');


        // 画面上のステータスが「勤務外」であることを確認
        $response->assertSee('勤務外');

        // 出勤処理を実行
        $response = $this->post('/attendance/start', [
            '_token' => csrf_token(), 
            // CSRFトークンを明示的に送信
        ]);

        // 出勤後の画面を取得し直す
        $response = $this->get('/attendance');

        // 出勤ボタンが消え、「出勤中」が表示されることを確認
        $response->assertDontSee('勤務外');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 出勤は1日1回のみで2回目はボタンが表示されない()
    {
        // テスト用ユーザー作成＆ログイン
        $user = User::factory()->create([
            'status_id' => 1, //勤務外
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');

        // 出勤処理を実行
        $response = $this->post('/attendance/start', [
            '_token' => csrf_token(), 
        ]);

        // 出勤後の画面を取得し直す
        $response = $this->get('/attendance');

        // 出勤中ステータスであることを確認
        $response->assertSee('出勤中');
        
        // 出勤ボタンを含むフォームがページに存在しないことを確認
        $response->assertDontSee('<form action="/attendance/start"'); // 出勤ボタンを含むフォームが存在しないことを確認

        // ステータスが「出勤中」に変更されていることを確認
        $user->refresh();
        $this->assertEquals(2, $user->status_id); // 出勤中

    }

    /** @test */
    public function 出勤時間が管理画面で確認できる()
    {
        // テスト用ユーザー作成＆ログイン
        $user = User::factory()->create([
            'status_id' => 1, //勤務外
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');


        // 画面上のステータスが「勤務外」であることを確認
        $response->assertSee('勤務外');

        // 出勤処理を実行
        $response = $this->post('/attendance/start', [
            '_token' => csrf_token(), 
            // CSRFトークンを明示的に送信
        ]);

        // 出勤後の画面を取得し直す
        $response = $this->get('/attendance');

        // 出勤ボタンが消え、「出勤中」が表示されることを確認
        $response->assertDontSee('勤務外');
        $response->assertSee('出勤中');

        // 管理者用のログイン
        $admin = Admin::factory()->create(); // 管理者ユーザーを作成
        $this->actingAs($admin, 'admin'); // 管理者としてログイン

        // 管理画面から出勤情報を確認
        $response = $this->get('/admin/attendance/list'); // 管理画面にアクセス

        // ここで実際の出勤記録を取得
        $attendance = $user->attendances()->latest()->first(); // 最新の出勤記録を取得

        // ユーザーの名前と出勤日時が表示されていることを確認
        $response->assertSee($user->name); // ユーザー名が表示されることを確認
        $response->assertSee($attendance->created_at->format('H:i')); // 出勤日時が表示されていることを確認（適切な形式で確認）
            
    }
}
