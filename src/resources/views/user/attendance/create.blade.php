@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/create.css') }}">
@endsection

@section('content')
<div class="attendance">
    <div class="status">{{ $userStatusName }}</div>
    <div class="date"> {{ $todayFormatted }} </div>
    <div class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>

    
    <form action="" class="action-form">
        @if ($userStatusName === '勤務外')
        <button type="submit" class="action-form__button">出勤</button>

        @elseif ($userStatusName === '勤務中')
        <button type="submit" class="action-form__button">退勤</button>
        <button type="submit" class="action-form__button action-form__button--break">休憩入</button>

        @elseif ($userStatusName === '休憩中')
        <button type="submit" class="action-form__button action-form__button--break">休憩戻</button>

        @elseif ($userStatusName === '退勤済')
        <p class="action-form__message">お疲れ様でした</p>

        @endif
    </form>
</div>

@endsection
