{{-- 勤怠詳細：ユーザー修正フォーム --}}


@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/common/attendance-show.css') }}">
<link rel="stylesheet" href="{{ asset('css/common/btn.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>
        
    <form action="{{ route('attendance-request.store', ['id' => $attendance->id]) }}" method="POST" class="attendance-detail__form">
        @csrf
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
                <td>
                    <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->formatted_clock_in) }}" class="attendance-detail__input">
                    @error('clock_in')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </td>
                <td>〜</td>
                <td>
                    <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->formatted_clock_out) }}" class="attendance-detail__input">
                    @error('clock_out')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            @foreach ($attendance->breakTimes as $break)
                <tr>
                    <th>休憩{{ $loop->iteration }}</th>
                    <td>
                        <input type="time" name="breaks[{{ $loop->index }}][break_start]" value='{{ old("breaks.{$loop->index}.break_start", $break->formatted_break_start) }}', class="attendance-detail__input">
                        @error("breaks.{$loop->index}.break_start")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </td>
                    <td>〜</td>
                    <td>
                        <input type="time" name="breaks[{{ $loop->index }}][break_end]" value='{{ old("breaks.{$loop->index}.break_end", $break->formatted_break_end) }}', class="attendance-detail__input">
                        @error("breaks.{$loop->index}.break_end")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            @endforeach
            <tr>
                <th>休憩追加</th>
                <td>
                    <input type="time" name="breaks[{{ count($attendance->breakTimes) }}][break_start]" class="attendance-detail__input">
                    @error('breaks.' . count($attendance->breakTimes) . '.break_start')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </td>
                <td>〜</td>
                <td>
                    <input type="time" name="breaks[{{ count($attendance->breakTimes) }}][break_end]" class="attendance-detail__input">
                    @error('breaks.' . count($attendance->breakTimes) . '.break_start')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td colspan="3">
                    <textarea name="request_reason" class="attendance-detail__input--reason"></textarea>
                    @error('breaks.' . count($attendance->breakTimes) . '.break_end')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>
        <div class="button-wrap">
            <button class="button">修正</button>
        </div>
    </form>

@endsection