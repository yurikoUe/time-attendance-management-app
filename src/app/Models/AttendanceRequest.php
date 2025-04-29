<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    // 明示的にテーブル名を指定
    protected $table = 'attendance_requests';

    protected $fillable = ['attendance_id', 'request_reason', 'status_id',  'is_admin_created'];

    // リレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function status()
    {
        return $this->belongsTo(AttendanceRequestStatus::class);
    }

    public function details()
    {
        return $this->hasOne(AttendanceRequestDetail::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceRequestBreak::class);
    }

    // 処理
    public function formattedClockIn()
    {
        return $this->details && $this->details->after_clock_in
            ? date('H:i', strtotime($this->details->after_clock_in))
            : $this->attendance->formatted_clock_in;
    }

    public function formattedClockOut()
    {
        return $this->details && $this->details->after_clock_out
            ? date('H:i', strtotime($this->details->after_clock_out))
            : $this->attendance->formatted_clock_out;
    }

    public function formattedBreaks()
    {
        $breaks = [];

        if ($this->breaks->isNotEmpty()) {
            foreach ($this->breaks as $breakRequest) {
                $breaks[] = (object)[
                    'formatted_break_start' => $breakRequest->after_break_start ? date('H:i', strtotime($breakRequest->after_break_start)) : null,
                    'formatted_break_end' => $breakRequest->after_break_end ? date('H:i', strtotime($breakRequest->after_break_end)) : null,
                ];
            }
        } else {
            foreach ($this->attendance->breakTimes as $break) {
                $breaks[] = (object)[
                    'formatted_break_start' => optional($break->break_start)->format('H:i'),
                    'formatted_break_end' => optional($break->break_end)->format('H:i'),
                ];
            }
        }

        return $breaks;
    }


}
