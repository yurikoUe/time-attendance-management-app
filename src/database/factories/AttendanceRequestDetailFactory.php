<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceRequestDetail;
use App\Models\AttendanceRequest;

class AttendanceRequestDetailFactory extends Factory
{
    protected $model = AttendanceRequestDetail::class;

    public function definition(): array
    {
        return [
            'attendance_request_id' => AttendanceRequest::factory(),
            'before_clock_in'       => '09:00:00',
            'after_clock_in'        => '09:30:00',
            'before_clock_out'      => '18:00:00',
            'after_clock_out'       => '18:30:00',
        ];
    }
}
