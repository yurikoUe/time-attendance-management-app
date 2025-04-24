@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common/attendance-show.css') }}">
<link rel="stylesheet" href="{{ asset('css/common/btn.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>

    @if ($isRequestPending)
        @include('shared.attendance.user-readonly')
    @else
        @include('shared.attendance.user-edit-form')
    @endif
</div>
@endsection
