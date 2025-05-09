<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;
use HasFactory;
use Illuminate\Foundation\Testing\WithoutMiddleware;





class BreakFunctionTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

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
            'email_verified_at' => now()->subHours(3),
        ]);
        $this->actingAs($user); // ユーザーとしてログイン

        // 出勤レコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(5),
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

        $user->refresh();  // DBの最新状態を取得
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status_id' => 3, // 期待する「休憩中」のステータス
        ]);

        $response = $this->get('/attendance');

        // 出勤後の画面を取得し直す
        // ステータスが「休憩中」になることを確認
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は1日何回でもできる()
    {
        $user = User::factory()->create(['status_id' => 2]);
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(5),
        ]);

        // 1回目の休憩
        $this->post('/breaks/start', ['_token' => csrf_token()]);
        $this->post('/breaks/end', ['_token' => csrf_token()]);

        // 2回目の休憩
        $this->post('/breaks/start', ['_token' => csrf_token()]);
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create(['status_id' => 2]);
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(5),
        ]);

        $this->post('/breaks/start', ['_token' => csrf_token()]);
        $this->post('/breaks/end', ['_token' => csrf_token()]);

        $user->refresh();
        $this->assertEquals(2, $user->status_id); // 出勤中に戻っているか

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻は一日何回でもできる()
    {
        $user = User::factory()->create(['status_id' => 2]);
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(5),
        ]);

        // 1回目の休憩→戻
        $this->post('/breaks/start', ['_token' => csrf_token()]);
        $this->post('/breaks/end', ['_token' => csrf_token()]);

        // 2回目の休憩
        $this->post('/breaks/start', ['_token' => csrf_token()]);
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'status_id' => 2, //出勤中
            'email_verified_at' => now()->subHours(3),
        ]);
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(5),
        ]);

        // 勤怠記録から休憩の記録を取得
        $attendance = Attendance::where('user_id', $user->id)->latest()->first();

        // 休憩開始の処理
        $breakStartResponse = $this->post('/breaks/start');
        // 休憩開始時刻を取得（最新の休憩開始を取得）
        $breakStart = $attendance->breakTimes()->whereNotNull('break_start')->latest()->first();

        sleep(1);

        // 休憩終了の処理
        $breakEndResponse = $this->post('/breaks/end', ['_token' => csrf_token()]);
        // 休憩終了時刻を取得（最新の休憩終了を取得）
        $breakEnd = $attendance->breakTimes()->whereNotNull('break_end')->latest()->first();

        // 休憩開始と終了
        $breakStart = BreakTime::where('attendance_id', $attendance->id)->whereNotNull('break_start')->first();
        $breakEnd = BreakTime::where('attendance_id', $attendance->id)->whereNotNull('break_end')->first();

        // 合計休憩時間を計算
        $breakDurationInMinutes = $breakStart->break_start->diffInMinutes($breakEnd->break_end);
        $breakDuration = sprintf('%d:%02d', floor($breakDurationInMinutes / 60), $breakDurationInMinutes % 60);

        // 勤怠一覧画面を取得
        $response = $this->get('/attendance/list');

        // 合計休憩時間が表示されていることを確認
        $response->assertSee($breakDuration);
    }


}
