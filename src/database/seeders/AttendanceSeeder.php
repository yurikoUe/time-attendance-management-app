<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //全全ユーザーのレコードを作成
        $users = User::all();

        foreach ($users as $user){
            $this->createAttendanceRecords($user);
        }

    }

    /**
     * 勤怠データを作成（通常勤務、日を跨ぐ勤務、3ヶ月分のデータ）
     */
    private function createAttendanceRecords(User $user)
    {
        // 通常勤務（２日前）
        $this->createAttendance($user, Carbon::today()->subDay(2), 9, 18);

        // 深夜勤務・日を跨ぐ勤務
        $this->createAttendance($user, Carbon::today()->subDay(5), 22, 6, true);

        // 2ヶ月前から今日までのデータ
        $startDate = Carbon::today()->subMonths(2);
        $endDate = Carbon::yesterday();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $this->createAttendance($user, $date->copy(), 9, 18);
        }
    }

    /**
     * 勤怠データを1件作成
     *
     * @param User $user 対象のユーザー
     * @param Carbon $date 勤務開始日（work_date に保存）
     * @param int $startHour 出勤時間の時刻（時）
     * @param int $endHour 退勤時間の時刻（時）
     * @param bool $crossMidnight 日を跨ぐ勤務かどうか（デフォルト: false）
     * @return void
     */
    private function createAttendance(User $user, Carbon $date, int $startHour, int $endHour, bool $crossMidnight = false)
    {

        $clockIn = (clone $date)->setHour($startHour)->setMinute(0);
        $clockOut = (clone $clockIn)->addHours($endHour - $startHour);

        // 日を跨ぐデータを作成するため、clock_out を翌日にする
        if ($crossMidnight) {
            $clockOut->addDay();
        }

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date->toDateString(),//YYYY-MM-DD 形式で保存
            'clock_in' => $clockIn->toDateTimeString(),// YYYY-MM-DD HH:MM:SS 形式で保存
            'clock_out' => $clockOut->toDateTimeString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
