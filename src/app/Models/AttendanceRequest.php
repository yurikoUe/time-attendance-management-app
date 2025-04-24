<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    // 明示的にテーブル名を指定（変更後）
    protected $table = 'attendance_requests';

    protected $fillable = ['attendance_id', 'request_reason', 'status_id',  'is_admin_created'];

    // 出勤記録とのリレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // 申請ステータスとのリレーション
    public function status()
    {
        return $this->belongsTo(AttendanceRequestStatus::class);
    }

    // 申請詳細
    public function details()
    {
        return $this->hasOne(AttendanceRequestDetail::class);
    }

    // 休憩情報
    public function breaks()
    {
        return $this->hasMany(AttendanceRequestBreak::class);
    }
}
