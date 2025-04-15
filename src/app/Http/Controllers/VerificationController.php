<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VerificationController extends Controller
{
    // メール認証ページの表示
    public function notice()
    {
        return view('user.email-verification');
    }

    // メール認証の再送信
    public function resend()
    {
        if (auth()->user() && !auth()->user()->hasVerifiedEmail()) {
            auth()->user()->sendEmailVerificationNotification();
            return back()->with('status', 'verification-link-sent');
        }

        return redirect()->route('verification.notice');
    }
}
