<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequestBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_request_id',
        'before_break_start',
        'after_break_start',
        'before_break_end',
        'after_break_end',
    ];

    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }

}
