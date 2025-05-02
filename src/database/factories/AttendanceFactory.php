<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $date = Carbon::today();
        $clockIn = $date->copy()->addHours(9);  // 9:00 出勤
        $clockOut = $date->copy()->addHours(18); // 18:00 退勤

        return [
            'user_id' => User::factory(),
            'work_date' => $date->toDateString(), // YYYY-MM-DD
            'clock_in' => $clockIn->toDateTimeString(), // YYYY-MM-DD HH:MM:SS
            'clock_out' => $clockOut->toDateTimeString(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }


}
