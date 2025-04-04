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
        //ID 1以外のユーザーのレコードを作成
        $users = User::where('id', '!=', 1)->get();

        foreach ($users as $user){
            $this->createAttendanceRecords($user);
        }

        //ID 1のユーザのレコードを作成（月を跨ぐデータ40件）
        $user = User::find(1);
        if (!$user)return; //ユーザーが存在しない場合は処理を終了

        $this->createMonthlyAttendanceRecords($user);
    }

    /**
     * 通常勤務 & 日を跨ぐ勤務データを作成
     */
    private function createAttendanceRecords(User $user)
    {
        // 通常の日勤パターン
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->subDays(2), // 2日前
            'clock_in' => Carbon::today()->subDays(2)->setHour(9)->setMinute(0), // 朝9時出勤
            'clock_out' => Carbon::today()->subDays(2)->setHour(18)->setMinute(0), // 18時退勤
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 深夜勤務・日を跨ぐパターン
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::yesterday(), // 昨日
            'clock_in' => Carbon::yesterday()->setHour(22)->setMinute(0), // 夜22時出勤
            'clock_out' => Carbon::today()->setHour(6)->setMinute(0), // 翌朝6時退勤
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createMonthlyAttendanceRecords(User $user)
    {
        $startDate = Carbon::today()->subMonths(2); //2ヶ月前から開始

        for ($i = 0; $i < 40; $i++) { 
            $workDate = (clone $startDate)->addDays($i); //1日づつ追加

            $clockIn = (clone $workDate)->setHour(rand(8,10))->setMinute(0); //8~10時の間に出勤
            $clockOut = (clone $clockIn)->addHours(rand(8,10)); //8~10時間勤務

            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $workDate->toDateString(),//YYYY-MM-DD 形式で保存
                'clock_in' => $clockIn->toDateTimeString(),// YYYY-MM-DD HH:MM:SS 形式で保存
                'clock_out' => $clockOut->toDateTimeString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
