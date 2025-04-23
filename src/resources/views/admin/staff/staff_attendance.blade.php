@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common/attendance-index.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/csv-btn.css') }}">
@endsection

@section('content')

<div class="attendance__container">
    <h1 class="attendance__title">{{ $user->name }}さんの勤怠</h1>

    <div class="attendance__navigation">
        <a href="{{ route('staff.attendance', ['id' => $user->id, 'month' => $prevMonth]) }}" class="attendance__nav-link attendance__nav-link--prev">←前月</a>

        <div class="attendance__display">
            <img src="{{ asset('images/calendar.svg') }}" alt="カレンダーアイコン" class="attendance__calendar-icon">
            <span class="attendance__text">{{ $currentMonth->format('Y/m') }}</span>
        </div>

        <a href="{{ route('staff.attendance', ['id' => $user->id, 'month' => $nextMonth]) }}" class="attendance__nav-link attendance__nav-link--next">→翌月</a>
    </div>

    <table class="attendance__table">
        <tr class="attendance__table-header">
            <th class="attendance__table-cell">日付</th>
            <th class="attendance__table-cell">出勤</th>
            <th class="attendance__table-cell">退勤</th>
            <th class="attendance__table-cell">休憩</th>
            <th class="attendance__table-cell">合計</th>
            <th class="attendance__table-cell">詳細</th>
        </tr>
        @foreach ($attendances as $attendance)
        <tr class="attendance__table-row">
            <td class="attendance__table-cell">
                {{ $attendance->formatted_work_date }}
            </td>
            <td class="attendance__table-cell">
                @if ($attendance->clock_in)
                    {{ $attendance->clock_in->format('H:i') }}
                @endif
            </td>
            <td class="attendance__table-cell">
                @if ($attendance->clock_out)
                    {{ $attendance->clock_out->format('H:i') }}
                @endif
            </td>
            <td class="attendance__table-cell">
                {{ $attendance->total_break_time ?? '' }}
            </td>
            <td class="attendance__table-cell">
                {{ $attendance->total_work_time ?? '' }}
            </td>
            <td class="attendance__table-cell">
                <a href="{{ route('attendance.show', ['id' => $attendance->id]) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </table>
    <div class="attendance__csv-btn">
        <form method="GET" action="{{ route('attendance.exportCsv', ['id' => $user->id, 'month' => $currentMonth->format('Y-m')]) }}">
            <button type="submit" class="btn-primary">CSV出力</button>
        </form>
    </div>
</div>

@endsection
