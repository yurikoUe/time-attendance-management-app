<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Database\Seeders\UserStatusesTableSeeder;
use App\Models\User;
use Illuminate\Support\Facades\Mail;


class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 外部キー制約を一時的に無効化
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    }

    /** @test */
    public function 名前が未入力の場合はバリデーションメッセージが表示される()
    {
        $response = $this->withoutMiddleware()->post('/register', [
            'name' => '',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
        $errors = $response->getSession()->get('errors')->get('name');
        $this->assertContains('お名前を入力してください', $errors);
    }

    /** @test */
    public function メールアドレスが未入力の場合、バリデーションメッセージが表示される()
    {
        $response = $this->withoutMiddleware()->post('/register',[
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $errors = $response->getSession()->get('errors')->get('email');
        $this->assertContains('メールアドレスを入力してください', $errors);
    }

    /** @test */
    public function パスワードが8文字未満の場合バリデーションメッセージが表示される()
    {
        $response = $this->withoutMiddleware()->post('/register',[
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
        $errors = $response->getSession()->get('errors')->get('password');
        $this->assertContains('パスワードは8文字以上で入力してください', $errors);
    }

    /** @test */
    public function パスワードが一致しない場合バリデーションメッセージが表示される()
    {
        $response = $this->withoutMiddleware()->post('/register',[
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpass',
        ]);

        $response->assertSessionHasErrors('password');
        $errors = $response->getSession()->get('errors')->get('password');
        $this->assertContains('パスワードと一致しません', $errors);
    }

    /** @test */
    public function パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->withoutMiddleware()->post('/register',[
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $errors = $response->getSession()->get('errors')->get('password');
        $this->assertContains('パスワードを入力してください', $errors);
    }

    /** @test */
    public function フォームの入力が正しい場合ユーザーが正常に保存される()
    {

        $response = $this->withoutMiddleware()->post('/register',[
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users',[
            'email' => 'user@example.com',
            'name' => 'テストユーザー'
        ]);
    }
}
