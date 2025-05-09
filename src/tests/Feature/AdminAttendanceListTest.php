<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
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

        // 管理者とユーザー作成
        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();

        // 勤怠データ（当日・前日・翌日）
        Attendance::factory()->createMany([
            [
                'user_id' => $this->user->id,
                'work_date' => Carbon::today(),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ],
            [
                'user_id' => $this->user->id,
                'work_date' => Carbon::yesterday(),
                'clock_in' => '08:30:00',
                'clock_out' => '17:30:00',
            ],
            [
                'user_id' => $this->user->id,
                'work_date' => Carbon::tomorrow(),
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
            ],
        ]);
    }

    /** @test */
    public function 🔳その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $response = $this->actingAs($this->admin, 'admin')
                        ->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee($this->user->name); // ユーザー名が表示されていること
    }

    /** @test */
    public function 🔳遷移した際に現在の日付が表示される()
    {
        $response = $this->actingAs($this->admin, 'admin')
                         ->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(Carbon::today()->format('Y/m/d')); // 例: 2025/05/08
    }

    /** @test */
    public function 🔳「前日」を押下した時に前日の勤怠情報が表示される()
    {
        $date = Carbon::yesterday()->format('Y-m-d');
        $response = $this->actingAs($this->admin, 'admin')
                         ->get("/admin/attendance/list?date={$date}");

        $response->assertStatus(200);
        $response->assertSee('08:30');
        $response->assertSee('17:30');
        $response->assertSee(Carbon::yesterday()->format('Y/m/d'));
    }

     /** @test */
    public function 🔳「翌日」を押下した時に前日の勤怠情報が表示される()
    {
        $date = Carbon::tomorrow()->format('Y-m-d');
        $response = $this->actingAs($this->admin, 'admin')
                         ->get("/admin/attendance/list?date={$date}");

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee(Carbon::tomorrow()->format('Y/m/d'));
    }
}
