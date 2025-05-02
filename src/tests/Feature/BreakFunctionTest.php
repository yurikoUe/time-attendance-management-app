<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use HasFactory;





class BreakFunctionTest extends TestCase
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
    public function 休憩ボタンが表示されて正しく休憩できる()
    {

        // ユーザーと初期ステータス（出勤中）を作成
        $user = User::factory()->create([
            'status_id' => 2, //出勤中
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user); // ユーザーとしてログイン

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(3),
        ]);

        // 勤怠打刻画面を開く
        $response = $this->get('/attendance');

        // 「休憩入ボタン」があることを確認
        $response->assertSee('休憩入');

        // 休憩入処理を実行
        $breakStartResponse = $this->post('/breaks/start', [
            '_token' => csrf_token(), 
            // CSRFトークンを明示的に送信
        ]);
        $breakStartResponse->assertRedirect();

        $this->assertDatabaseHas('users', [
    'id' => $user->id,
    'status_id' => 3, // 期待する「休憩中」のステータス
]);

        dd($user->status_id);
        // 出勤後の画面を取得し直す
        $response = $this->get('/attendance');

        // ステータスが「休憩中」になることを確認
        $response->assertSee('休憩中');
    }
}
