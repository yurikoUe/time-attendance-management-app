<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Attendance $attendance;

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
            'name' => 'テスト太郎',
            'status_id' => 2,
            'email_verified_at' => now(),
        ]);

        // 勤怠データ作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::parse('2025-05-01'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 休憩データ作成
        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
    }

    /** @test */
    public function 勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        $response = $this->actingAs($this->user)
                         ->get("/attendance/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    /** @test */
    public function 勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::create(2025, 5, 1),
        ]);

        $response = $this->actingAs($this->user)
                        ->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);

        // 「2025年」と「5月1日」が表示されているかチェック
        $response->assertSee('2025年');
        $response->assertSee('5月1日');
    }


    /** @test */
    public function 「出勤・退勤」にて表示されている時間がログインユーザーの打刻と一致している()
    {
        $response = $this->actingAs($this->user)
                         ->get("/attendance/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

     /** @test */
    public function 「休憩」にて示されている時間がログインユーザーの打刻と一致している()
    {
        $response = $this->actingAs($this->user)
                         ->get("/attendance/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
