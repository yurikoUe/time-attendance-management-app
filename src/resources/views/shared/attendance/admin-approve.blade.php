@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common/attendance-show.css') }}">
<link rel="stylesheet" href="{{ asset('css/common/btn.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細（管理者）</h1>

    <table class="attendance-detail__table">
        <tr>
            <th>名前</th>
            <td colspan="3">{{ $attendance->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ $attendance->formatted_work_date_year }}</td>
            <td></td>
            <td>{{ $attendance->formatted_work_date_month_day }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>{{ $attendance->formatted_clock_in }}</td>
            <td>〜</td>
            <td>{{ $attendance->formatted_clock_out }}</td>
        </tr>
        @foreach ($attendance->breakTimes as $break)
            <tr>
                <th>休憩{{ $loop->iteration }}</th>
                <td>{{ $break->formatted_break_start }}</td>
                <td>〜</td>
                <td>{{ $break->formatted_break_end }}</td>
            </tr>
        @endforeach
        <tr>
            <th>備考（申請理由）</th>
            <td colspan="3">{{ $attendance->attendanceRequests->first()->request_reason }}</td>
        </tr>
    </table>

    <form action="" method="POST" class="attendance-detail__form">
        @csrf
        <div class="button-wrap">
            <button name="action" value="approve" class="button">承認</button>
        </div>
    </form>
</div>
@endsection
