<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => '山田 太郎',
            'email' => 'taro@example.com',
            'password' => Hash::make('password'),
            'status_id' => 1, 
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'name' => '西 伶奈',
            'email' => 'reina@example.com',
            'password' => Hash::make('password'),
            'status_id' => 1,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'name' => '増田 一世',
            'email' => 'masuda@example.com',
            'password' => Hash::make('password'),
            'status_id' => 1,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'name' => '山本 敬吉',
            'email' => 'yamamoto@example.com',
            'password' => Hash::make('password'),
            'status_id' => 1,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'name' => '秋田 朋美',
            'email' => 'tomomi@example.com',
            'password' => Hash::make('password'),
            'status_id' => 1,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        User::create([
            'name' => '中西 敏夫',
            'email' => 'toshio@example.com',
            'password' => Hash::make('password'),
            'status_id' => 1, 
            'email_verified_at' => null, // 未認証
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
