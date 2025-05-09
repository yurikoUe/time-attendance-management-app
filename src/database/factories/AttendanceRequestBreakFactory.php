<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceRequestBreak;
use App\Models\AttendanceRequest;

class AttendanceRequestBreakFactory extends Factory
{
    protected $model = AttendanceRequestBreak::class;

    public function definition(): array
    {
        return [
            'attendance_request_id' => AttendanceRequest::factory(),
            'before_break_start'    => '12:00:00',
            'after_break_start'     => '13:00:00',
            'before_break_end'      => '13:00:00',
            'after_break_end'       => '14:00:00',
        ];
    }
}
