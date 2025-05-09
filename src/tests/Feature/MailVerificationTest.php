<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class MailVerificationTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware; 

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('user_statuses')->insert([
            ['id' => 1, 'name' => '勤務外'],
            ['id' => 2, 'name' => '出勤中'],
            ['id' => 3, 'name' => '休憩中'],
            ['id' => 4, 'name' => '退勤済'],
        ]);

    }

    /** @test */
    public function 会員登録後、認証メールが送信される()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);

        $user = \App\Models\User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
    }

    /** @test */
    public function メール認証誘導画面で_認証はこちら_ボタンが正しく表示され_リンク先が認証サイトになっている()
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);

        // 「認証はこちら」リンクが表示され、正しいURLが埋め込まれていることを確認
        $response->assertSee('認証はこちらから');
        $this->assertStringContainsString('href="http://localhost:8025/"', $response->getContent());

    }

    /** @test */
    public function メール認証サイトのメール認証を完了すると_勤怠一覧ページに遷移する()
    {
        Event::fake();

        $user = User::factory()->unverified()->create();

        // メール認証リンクを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 実行と検証
        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertStringStartsWith(
            url('/attendance'),
            $response->headers->get('Location')
        );
    }
}
