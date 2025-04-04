<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // すでにある勤怠データから適当に取得
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {

            $breakPattern = rand(0, 2); //0: 休憩なし, 1: 休憩１回, 2: 休憩２回

            //休憩なしの場合は何もしない

            if ($breakPattern >= 1){ //休憩１回の場合
                
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => Carbon::parse($attendance->clock_in)->addHours(3), // 出勤から3時間後に休憩開始
                    'break_end' => Carbon::parse($attendance->clock_in)->addHours(3)->addMinutes(30), // 30分休憩
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($breakPattern >= 2){ //休憩2回の場合
           
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => Carbon::parse($attendance->clock_in)->addHours(6), // 出勤から6時間後
                    'break_end' => Carbon::parse($attendance->clock_in)->addHours(6)->addMinutes(15), // 15分休憩
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
