@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common/attendance-show.css') }}">
<link rel="stylesheet" href="{{ asset('css/common/btn.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細（管理者）</h1>

    @if ($isEditMode)
        @include('shared.attendance.admin-edit-form')
    @else
        @include('shared.attendance.admin-approve')
    @endif
</div>
@endsection
