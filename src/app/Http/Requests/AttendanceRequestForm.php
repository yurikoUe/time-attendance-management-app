<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;


class AttendanceRequestForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'request_reason' => 'required|string|max:255',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'breaks.*.break_start' => 'nullable|date_format:H:i',
            'breaks.*.break_end' => 'nullable|date_format:H:i',
        ];
    }

    public function messages()
    {
        return [
            'request_reason.required' => '備考を記入してください',
            'clock_in.date_format' => '出勤時間の形式が不正です（例：09:00）',
            'clock_out.date_format' => '退勤時間の形式が不正です（例：18:00）',
            'breaks.*.break_start.date_format' => '休憩開始時間の形式が不正です（例：12:00）',
            'breaks.*.break_end.date_format' => '休憩終了時間の形式が不正です（例：13:00）',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function($validator){
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            //出勤時間が退勤時間より後もしくは退勤時間が出勤時間より前の場合
            if ($clockIn && $clockOut) {
                $in = Carbon::createFromFormat('H:i', $clockIn);
                $out = Carbon::createFromFormat('H:i', $clockOut);

                if ($in >= $out) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            // 勤務時間内に休憩がおさまっているかをチェック
            $breaks = $this->input('breaks', []);
            foreach ($breaks as $index => $break) {
                $start = $break['break_start'] ?? null;
                $end = $break['break_end'] ?? null;

                // 休憩開始時間が出勤時間より前
                if ($start && $clockIn && $start < $clockIn) {
                    $validator->errors()->add("breaks.$index.break_start", '休憩時間が勤務時間外です');
                }

                // 休憩終了時間が退勤時間より後
                if ($end && $clockOut && $end > $clockOut) {
                    $validator->errors()->add("breaks.$index.break_end", '休憩時間が勤務時間外です');
                }

                // 休憩終了時間が休憩開始時間より前
                if ($start && $end && $start >= $end) {
                    $validator->errors()->add("breaks.$index.break_end", '休憩終了時間が休憩開始時間より後でなければなりません');
                }
            }
        });
    }
}
