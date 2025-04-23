{{-- 勤怠詳細：ユーザー（申請中／修正不可） --}}
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
        <th>備考</th>
        <td colspan="3">{{ $attendance->attendanceRequests->first()->request_reason }}</td>
    </tr>
</table>

<div class="attendance-detail__message">
    <p>*承認待ちのため修正できません</p>
</div>
