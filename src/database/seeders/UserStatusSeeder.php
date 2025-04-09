<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\UserStatus;

class UserStatusSeeder extends Seeder
{
    public function run()
    {
        DB::table('user_statuses')->insert([
            ['name' => '勤務外'],
            ['name' => '出勤中'],
            ['name' => '休憩中'],
            ['name' => '退勤済'],
        ]);
    }
}
