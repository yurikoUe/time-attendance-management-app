<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequestStatus extends Model
{
     use HasFactory;

    protected $fillable = ['name'];
    
    public function attendanceRequests()
    {
        return $this->hasMany(BreakTime::class);
    }
}
