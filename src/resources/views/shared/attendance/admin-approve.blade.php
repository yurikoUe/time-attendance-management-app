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
    <!-- 出勤・退勤・休憩、内容はuser-readonlyと同様でOK -->
    <!-- ... -->
    <tr>
        <th>備考（申請理由）</th>
        <td colspan="3">{{ $attendance->attendanceRequests->first()->request_reason }}</td>
    </tr>
</table>

<form action="{{ route('admin.attendance.approve', $attendance->id) }}" method="POST" class="attendance-detail__form">
    @csrf
    <div class="attendance-detail__button-wrap">
        <button name="action" value="approve" class="attendance-detail__button--approve">承認</button>
        <button name="action" value="reject" class="attendance-detail__button--reject">却下</button>
    </div>
</form>
