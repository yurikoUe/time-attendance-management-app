<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOrUser
{
    // public function handle($request, Closure $next)
    // {
    //     if (Auth::guard('web')->check() || Auth::guard('admin')->check()) {
    //         return $next($request);
    //     }

    //     return redirect()->route('login');
    // }

    public function handle($request, Closure $next)
    {
        // 管理者としてログインしているか、ユーザーとしてログインしているかを確認
        if (Auth::guard('admin')->check()) {
            // 管理者としてログインしていれば、管理者用のルートに遷移
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            // ユーザーとしてログインしていれば、ユーザー用のルートに遷移
            return $next($request);
        }

        // 両方の認証が通っていない場合はログイン画面へ
        return redirect()->route('login'); // または 'admin.login'
    }
}
