@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance-create.css') }}">
@endsection

@section('content')
@if(session('error'))
    <div class="message">
        {{ session('error') }}
    </div>
@endif


<div class="attendance">
    <div class="status">{{ $userStatusName }}</div>
    <div class="date"> {{ $todayFormatted }} </div>
    <div class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>

    @if ($userStatusName === '勤務外')
    <form action="{{ route('attendances.start') }}"  method="POST" class="action-form">
        @csrf
        <button type="submit" class="working-actions__button">出勤</button>
    </form>

    @elseif ($userStatusName === '出勤中')
    <div class="working-actions">
        <form action="{{ route('attendances.end') }}"  method="POST" class="action-form">
            @csrf
            <button type="submit" class="working-actions__button">退勤</button>
        </form>   
        <form action="{{ route('breaks.start') }}"  method="POST" class="action-form">
            @csrf
            <button type="submit" class="break-action-form__button">休憩入</button>
        </form>
    </div>

    @elseif ($userStatusName === '休憩中')
    <form action="{{ route('breaks.end') }}"  method="POST" class="action-form">
        @csrf
        <button type="submit" class="break-action-form__button">休憩戻</button>
    </form>

    @elseif ($userStatusName === '退勤済')
        <p class="action-form__message">お疲れ様でした。</p>
    @endif

</div>

@endsection
