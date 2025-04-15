<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequestDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_request_id',
        'before_clock_in',
        'after_clock_in',
        'before_clock_out',
        'after_clock_out',
    ];

    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }
}
