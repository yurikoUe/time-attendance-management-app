<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceRequestStatus;


class AttendanceRequestStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('attendance_request_statuses')->insert([
            ['name' => '承認待ち'],
            ['name' => '承認済み'],
        ]);
    }
}
