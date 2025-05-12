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

            if ($clockIn && $clockOut) {
                $in = Carbon::createFromFormat('H:i', $clockIn);
                $out = Carbon::createFromFormat('H:i', $clockOut);

                // 退勤が出勤よりも前なら、日付を+1日して比較
                if ($out <= $in) {
                    $out->addDay();
                }

                if ($in >= $out) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                }

                // 勤務時間内に休憩が収まっているかチェック
                $breaks = $this->input('breaks', []);
                foreach ($breaks as $index => $break) {
                    $startRaw = $break['break_start'] ?? null;
                    $endRaw = $break['break_end'] ?? null;

                    if ($startRaw) {
                        $start = Carbon::createFromFormat('H:i', $startRaw);
                        if ($start < $in) {
                            $start->addDay(); // 日を跨いでいる可能性
                        }
                    }

                    if ($endRaw) {
                        $end = Carbon::createFromFormat('H:i', $endRaw);
                        if ($end < $in) {
                            $end->addDay(); // 日を跨いでいる可能性
                        }
                    }

                    if (isset($start) && $start < $in) {
                        $validator->errors()->add("breaks.$index.break_start", '休憩時間が勤務時間外です');
                    }

                    if (isset($end) && $end > $out) {
                        $validator->errors()->add("breaks.$index.break_end", '休憩時間が勤務時間外です');
                    }

                    if (isset($start, $end) && $start >= $end) {
                        $validator->errors()->add("breaks.$index.break_start", '休憩時間が勤務時間外です');
                        $validator->errors()->add("breaks.$index.break_end", '休憩時間が勤務時間外です');
                    }
                }
            }
        });
    }

}
