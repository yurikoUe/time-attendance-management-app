@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="attendance__alert">
  {{-- エラーメッセージやフラッシュメッセージ用 --}}
</div>

<div>
    <h1>勤怠詳細</h1>

    @if ($isRequestPending)
        <table>
            <tr>
                <th>名前</th>
                <td colspan="3">{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td colspan="3">{{ $attendance->formatted_work_date_ymd }}</td>

            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{ $attendance->formatted_clock_in }}
                </td>
                <td>〜</td>
                <td>
                    {{ $attendance->formatted_clock_out }}
                </td>
            </tr>
            @foreach ($attendance->breakTimes as $break)
                <tr>
                    <th>休憩</th>
                    <td>
                        {{ $break->formatted_break_start }}
                    </td>
                    <td>〜</td>
                    <td>
                        {{ $break->formatted_break_end }}
                    </td>
                    
                </tr>
            @endforeach
            <tr>
                <th>備考</th>
                <td colspan="3">
                    {{ $attendance->attendanceRequests->first()->request_reason }}
                </td>
            </tr>
        </table>
        <p>*承認待ちのため修正できません</p>
    @else

        <form action="{{ route('attendance-request.store', ['id' => $attendance->id]) }}" method="POST">
            @csrf
            <table>
                <tr>
                    <th>名前</th>
                    <td colspan="3">{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td colspan="3">{{ $attendance->formatted_work_date_ymd }}</td>

                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <input type="time" name="clock_in" value="{{ $attendance->formatted_clock_in }}">
                    </td>
                    <td>〜</td>
                    <td>
                        <input type="time" name="clock_out" value="{{ $attendance->formatted_clock_out }}">
                    </td>
                </tr>
                @foreach ($attendance->breakTimes as $break)
                    <tr>
                        <th>休憩</th>
                        <td>
                            <input type="time" name="breaks[{{ $loop->index }}][break_start]" value="{{ $break->formatted_break_start }}">
                        </td>
                        <td>〜</td>
                        <td>
                            <input type="time" name="breaks[{{ $loop->index }}][break_end]" value="{{ $break->formatted_break_end }}">
                        </td>
                        
                    </tr>
                @endforeach
                <tr>
                    <th>備考</th>
                    <td colspan="3">
                        <textarea name="request_reason"></textarea>
                    </td>
                </tr>
            </table>
            <button>修正</button>
        </form>
    @endif
</div>

@endsection
