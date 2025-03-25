<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = ['attendance_id', 'request_reason', 'status_id'];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function status()
    {
        return $this->belongsTo(AttendanceRequestStatus::class);
    }

    
}
