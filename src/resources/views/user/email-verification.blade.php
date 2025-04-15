@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/email-verification.css') }}">
@endsection

@section('content')
@if (session('status') == 'verification-link-sent')
    <p class="verification__alert" role="alert">
        新しい認証リンクを送信しました。
    </p>
@endif

    <div class="verification">

        <p class="verification__message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <a href="http://localhost:8025/" target="_blank" class="verification__link btn btn-primary mb-3">
            認証はこちらから
        </a>

        <form method="POST" action="{{ route('verification.resend') }}" class="verification__form mb-3">
            @csrf
            <button type="submit" class="verification__button btn btn-outline-secondary">
                認証メールを再送する
            </button>
        </form>
    </div>
@endsection
