<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttendanceStatusTest extends TestCase
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
    public function 勤務外の場合、勤怠ステータスが正しく常時される()
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
    }

    /** @test */
    public function 出勤中の場合、勤怠ステータスが正しく常時される()
    {
        // テスト用ユーザー作成＆ログイン
        $user = User::factory()->create([
            'status_id' => 2, //出勤中
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        // 画面上のステータスが「出勤外」であることを確認
        $response->assertSee('出勤中');
    }

     /** @test */
    public function 休憩中の場合、勤怠ステータスが正しく常時される()
    {
        // テスト用ユーザー作成＆ログイン
        $user = User::factory()->create([
            'status_id' => 3, //休憩中
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        // 画面上のステータスが「出勤外」であることを確認
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済みの場合、勤怠ステータスが正しく常時される()
    {
        // テスト用ユーザー作成＆ログイン
        $user = User::factory()->create([
            'status_id' => 4, //退勤済
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        // 画面上のステータスが「出勤外」であることを確認
        $response->assertSee('退勤済');
    }
}
