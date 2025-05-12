@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common/attendance-index.css') }}">
@endsection

@section('content')

<div class="attendance__container">
    <h1 class="attendance__title">{{ $currentDate->format('Y年n月j日') }}の勤怠</h1>

    <div class="attendance__navigation">
        <a href="{{ route('admin.attendance.index', ['date' => $prevDate]) }}" class="attendance__nav-link attendance__nav-link--prev">←前日</a>

        <div class="attendance__display">
            <img src="{{ asset('images/calendar.svg') }}" alt="カレンダーアイコン" class="attendance__calendar-icon">
            <span class="attendance__text">{{ $currentDate->format('Y/m/d') }}</span>
        </div>

        <a href="{{ route('admin.attendance.index', ['date' => $nextDate]) }}" class="attendance__nav-link attendance__nav-link--next">→翌日</a>
    </div>

    <table class="attendance__table">
        <tr class="attendance__table-header">
            <th class="attendance__table-cell">名前</th>
            <th class="attendance__table-cell">出勤</th>
            <th class="attendance__table-cell">退勤</th>
            <th class="attendance__table-cell">休憩</th>
            <th class="attendance__table-cell">合計</th>
            <th class="attendance__table-cell">詳細</th>
        </tr>
        @foreach ($attendances as $attendance)
        <tr  class="attendance__table-row">
            <td class="attendance__table-cell">{{ $attendance->user->name }}</td>
            <td class="attendance__table-cell">{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}</td>
            <td class="attendance__table-cell">{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}</td>
            <td class="attendance__table-cell">{{ $attendance->total_break_time ?? '' }}</td>
            <td class="attendance__table-cell">{{ $attendance->total_work_time ?? '' }}</td>
            <td class="attendance__table-cell"><a href="{{ route('attendance.show', ['id' => $attendance->id]) }}">詳細</td>
            
        </tr>
        @endforeach
    </table>
</div>

@endsection
