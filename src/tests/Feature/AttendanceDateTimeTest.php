<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;



class AttendanceDateTimeTest extends TestCase
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
    public function 現在の日時情報がUIと同じ形式で出力されている()
    {
        // テスト用ユーザー作成＆ログイン
        $user = User::factory()->create([
            'status_id' => 1,
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        // 現在日時の整形（ビューと同じ形式に揃える）
        $now = Carbon::now();
        $formattedDate = $now->format('Y年n月j日') . '(' . $now->locale('ja')->isoFormat('ddd') . ')';
        $formattedTime = $now->format('H:i'); 

        // 勤怠打刻画面にアクセス
        $response = $this->get('/attendance');

        // ステータス確認
        $response->assertStatus(200);
        
        // 日付と時間が表示されているか確認
        $response->assertSee($formattedDate);
        $response->assertSee($formattedTime);

    }
}
