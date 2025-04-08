<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = ['attendance_id', 'break_start', 'break_end'];

    // Carbon インスタンスにキャストし、日付や時間の操作を簡単に行えるようにする
    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    
}
