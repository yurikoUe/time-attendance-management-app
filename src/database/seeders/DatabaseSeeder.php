<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UserStatusSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserStatusSeeder::class);
    }
}
