<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ユーザー登録
        Fortify::createUsersUsing(CreateNewUser::class);
        
        // 登録画面（ユーザー用）
        Fortify::registerView(function () {
            return view('user.register');
        });

        // ログイン画面の振り分け（ユーザー or 管理者）
        Fortify::loginView(function (Request $request) {
            return $request->is('admin/*') 
                ? view('admin.login') // 管理者用ログイン画面
                : view('user.login'); // ユーザー用ログイン画面
        });

        Fortify::authenticateUsing(function (Request $request) {
            // 管理者ルートでないなら、通常のユーザー認証へ
            if (!$request->is('admin/*')) {
                return Auth::attempt($request->only('email', 'password')) 
                    ? Auth::user() 
                    : null;
            }

            // 管理者認証
            $admin = Admin::where('email', $request->email)->first();
            return ($admin && password_verify($request->password, $admin->password))
                ? $admin
                : null;
        });

        // ログイン試行回数の制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}
