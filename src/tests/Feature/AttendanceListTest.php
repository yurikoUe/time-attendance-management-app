<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        DB::table('user_statuses')->insert([
            ['id' => 1, 'name' => '勤務外'],
            ['id' => 2, 'name' => '出勤中'],
            ['id' => 3, 'name' => '休憩中'],
            ['id' => 4, 'name' => '退勤済'],
        ]);

        // ユーザー作成
        $this->user = User::factory()->create([
            'status_id' => 2, // 出勤中
            'email_verified_at' => now(),
        ]);

        // 勤怠データ作成
        Attendance::factory()->createMany([
            [
                'user_id' => $this->user->id,
                'work_date' => Carbon::now()->subMonth()->startOfMonth(),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ],
            [
                'user_id' => $this->user->id,
                'work_date' => Carbon::now()->startOfMonth(),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ],
            [
                'user_id' => $this->user->id,
                'work_date' => Carbon::now()->addMonth()->startOfMonth(),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ],
        ]);
        
    }

    /** @test */
    public function 自分が行った勤怠情報が全て表示されている()
    {
        $response = $this->actingAs($this->user)
                         ->get('/attendance/list');

        $response->assertStatus(200);

        // フォーマットを変更
        $response->assertSee(Carbon::now()->format('Y/m'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $response = $this->actingAs($this->user)
                        ->get('/attendance/list');

        $response->assertStatus(200);

        // 現在の月が表示されていることを確認
        $response->assertSee(Carbon::now()->format('Y/m')); // 2025/05形式
    }

    /** @test */
    public function 「前月」を押下した時に表示つきの前月の情報が表示される()
    {
        $response = $this->actingAs($this->user)
                        ->get('/attendance/list?month=' . Carbon::now()->subMonth()->format('Y-m'));

        $response->assertStatus(200);

        // 前月の月が表示されていることを確認
        $response->assertSee(Carbon::now()->subMonth()->format('Y/m')); // 2025/04形式
    }

    /** @test */
    public function 「翌月」を押下した時に表示つきの翌月の情報が表示される()
    {
        $response = $this->actingAs($this->user)
                        ->get('/attendance/list?month=' . Carbon::now()->addMonth()->format('Y-m'));

        $response->assertStatus(200);

        // 翌月の月が表示されていることを確認
        $response->assertSee(Carbon::now()->addMonth()->format('Y/m')); // 2025/06形式
    }

    /** @test */
    public function 「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        // 勤怠データを取得
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
        ]);
        
        $response = $this->withoutMiddleware()
                         ->actingAs($this->user)
                         ->get('/attendance/list');

        $response->assertStatus(200);

        // 「詳細」ボタンがその日の勤怠詳細ページへ遷移することを確認
        $response->assertSee('詳細');

        // 生成される詳細リンクが正しいURLになっていることを確認
        $response->assertSee(route('attendance.show', ['id'  => $attendance->id])); // 修正点
    }

}
