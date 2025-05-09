<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class AdminStaffAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected User $user;
    protected $attendances = [];

    public function setUp(): void
    {
        parent::setUp();

        DB::table('user_statuses')->insert([
            ['id' => 1, 'name' => '勤務外'],
            ['id' => 2, 'name' => '出勤中'],
            ['id' => 3, 'name' => '休憩中'],
            ['id' => 4, 'name' => '退勤済'],
        ]);

        $this->admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // 一般ユーザー作成
        $this->user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'status_id' => 2,
            'email_verified_at' => now(),
        ]);

        // 勤怠データを3ヶ月分生成
        $this->attendances = Attendance::factory()->createMany([
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
    public function ユーザー情報取得機能（管理者）()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
    }

     /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get("/admin/attendance/staff/{$this->user->id}");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $month = Carbon::now()->subMonth()->format('Y-m');
        $response = $this->actingAs($this->admin, 'admin')
                        ->get("/admin/attendance/staff/{$this->user->id}?month={$month}");

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->subMonth()->format('Y/m')); // 2025/04 形式
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        $month = Carbon::now()->addMonth()->format('Y-m');
        $response = $this->actingAs($this->admin, 'admin')
                        ->get("/admin/attendance/staff/{$this->user->id}?month={$month}");

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->addMonth()->format('Y/m')); // 2025/06 形式
    }

    /** @test */
    public function 「詳細」を押下するとその日の勤怠詳細画面に遷移する()
    {
        $attendance = $this->attendances[1]; 

        $response = $this->actingAs($this->admin, 'admin')
                ->get("/admin/attendance/staff/{$this->user->id}");

        $response->assertStatus(200);
        $response->assertSee('詳細');
        $response->assertSee("/attendance/{$attendance->id}");
    }
}
