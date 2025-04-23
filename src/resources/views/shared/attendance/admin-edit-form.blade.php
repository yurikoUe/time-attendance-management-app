{{-- 勤怠詳細：管理者が直接修正 --}}

<form action="" method="POST" class="attendance-detail__form">
    @csrf
    @method('PUT')
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
                    <input type="time" name="breaks[{{ $loop->index }}][break_start]" value="{{ old("breaks.{$loop->index}.break_start", $break->formatted_break_start) }}" class="attendance-detail__input">
                    @error("breaks.{$loop->index}.break_start")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </td>
                <td>〜</td>
                <td>
                    <input type="time" name="breaks[{{ $loop->index }}][break_end]" value="{{ old("breaks.{$loop->index}.break_end", $break->formatted_break_end) }}" class="attendance-detail__input">
                    @error("breaks.{$loop->index}.break_end")
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        @endforeach
        <tr>
            <th>休憩追加</th>
            <td><input type="time" name="breaks[{{ count($attendance->breakTimes) }}][break_start]" class="attendance-detail__input"></td>
            <td>〜</td>
            <td><input type="time" name="breaks[{{ count($attendance->breakTimes) }}][break_end]" class="attendance-detail__input"></td>
        </tr>
        <tr>
            <th>備考</th>
            <td colspan="3">
                <textarea name="request_reason" class="attendance-detail__input--reason"></textarea>
                @error('request_reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </td>
        </tr>
    </table>
    <div class="attendance-detail__button-wrap">
        <button class="attendance-detail__button">修正</button>
    </div>
</form>
