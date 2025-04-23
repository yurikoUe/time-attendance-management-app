<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOrUser
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            // 管理者 → OK（verified なしで通す）
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            // ユーザー → verified チェック
            if (! $request->user()->hasVerifiedEmail()) {
                // メール認証されてない → リダイレクト
                return redirect()->route('verification.notice');
            }

            return $next($request);
        }

        // どちらにもログインしてない → 403
        abort(403, 'Unauthorized');
    }

}
