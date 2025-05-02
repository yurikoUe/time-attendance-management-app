<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use Faker\Factory as Faker;


class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $admin = Admin::factory()->create([
            'password' => bcrypt('adminpass123'),
        ]);

        $response = $this->withoutMiddleware()->post('/admin/login', [
            'email' => '', //入力なし
            'password' => 'adminpass123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    public function パスワードが未入力の場合、バリデーションメッセージが表示される()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $response = $this->withoutMiddleware()->post('/admin/login', [
            'email' => 'admin@examle.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
        
    }

    /** @test */
    public function 登録内容と一致しない場合、バリデーションメッセージが表示される()
    {
        $faker = Faker::create();

        $admin = Admin::factory()->create([
            'email' => $faker->unique()->safeEmail,
            'password' => bcrypt('correctpassword'),
        ]);

        $response = $this->withoutMiddleware()->post('/admin/login', [
            'email' => 'wrong@examle.com',
            'password' => 'adminpass123',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = $response->getSession()->get('errors')->get('email');
        $this->assertContains('ログイン情報が登録されていません', $errors);
    }
}
