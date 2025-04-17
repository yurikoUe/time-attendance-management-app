<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

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
        Fortify::loginView(function (){
            if(request()->is('admin/*')){
                return view('admin.login');
            }
            return view('user.login');
        });

        Fortify::authenticateUsing(function (Request $request) {

            // 管理者ログインかどうかをURLde判定
            if ($request->is('admin/*')){
                $admin = Admin::where('email', $request->email)->first();
                
                if($admin && Hash::check($request->password, $admin->password)){
                    Auth::guard('admin')->login($admin);
                    return $admin; // Fortifyがadminガードでログイン
                } 
                
            } else {
                $user = User::where('email', $request->email)->first();
                if ($user && Hash::check($request->password, $user->password)){
                    return $user; // Fortifyがwebガードでログイン
                }
            }

            return null;
        });

        // ログイン試行回数の制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // 管理者ログイン用のPOSTルートを追加
        Route::post('/admin/login', function (Request $request) {
            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (Auth::guard('admin')->attempt([
                'email' => $request->email,
                'password' => $request->password,
            ], $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect('/admin/attendance/list');
            }

            return back()->withErrors([
                'email' => '管理者ログインに失敗しました。',
            ]);
        })->middleware(['web'])->name('admin.login.submit');
    }
}
