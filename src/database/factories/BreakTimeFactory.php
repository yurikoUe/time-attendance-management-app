<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(), // または後で手動で指定
            'break_start' => $this->faker->time('H:i:s', '13:00:00'),
            'break_end' => $this->faker->time('H:i:s', '14:00:00'),
        ];
    }
}
