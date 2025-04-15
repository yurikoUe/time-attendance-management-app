<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'work_date', 'clock_in', 'clock_out'
    ];

    //Carbon インスタンスにキャストし、日付や時間の操作を簡単に行えるようにする
    protected $casts = [
        'work_date' => 'date', // 自動で Carbon インスタンスにキャストされるように設定
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }


    //休憩時間の合計（分単位） を算出
    public function getTotalBreakMinutes()
    {
        return $this->breakTimes->sum(fn($break) => //sum()により複数回の休憩にも対応
            Carbon::parse($break->break_end)->diffInMinutes(Carbon::parse($break->break_start))
        );
    }
    //総勤務時間から休憩時間を引いた実働時間を計算するアクセサ
    public function getTotalWorkTimeAttribute()
    {
        // 出勤時間か退勤時間のどちらかが未入力なら '-'
        if(!$this->clock_in || !$this->clock_out) {
            return '-';
        }

        // 勤務時間（分単位）を計算
        $workMinutes = $this->clock_out->diffInMinutes($this->clock_in) - $this->getTotalBreakMinutes();

        // 勤務時間（分単位）を「H:i」形式（例：90分 → "1:30"）に変換して返す
        return floor($workMinutes / 60)  // 時間部分（分 ÷ 60）
        . ':' 
        . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT); // 分部分（余りの分を2桁にして表示）
    }

    // 休憩時間を "H:i"（例: 1:00, 0:45）形式で表示するアクセサ
    public function getTotalBreakTimeAttribute()
    {
        $totalBreakMinutes = $this->getTotalBreakMinutes();

        // 分を "H:i" 形式に変換（例：90分 → "1:30"）
        return floor($totalBreakMinutes / 60) 
        . ':' 
        . str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT);
    }

    // フォーマットされた日付「月/日(曜日)」を取得するアクセサ　例：05/01(木)
    public function getFormattedWorkDateAttribute()
    {
        return $this->work_date->format('m/d') . '(' . $this->work_date->locale('ja')->isoFormat('ddd') . ')';
    }

    // フォーマットされた日付「年」を取得するアクセサ　例：2025年
    public function getFormattedWorkDateYearAttribute()
    {
        return $this->work_date->format('Y年');
    }

    // フォーマットされた日付「月日」を取得するアクセサ　例：5月1日　
    public function getFormattedWorkDateMonthDayAttribute()
    {
        return $this->work_date->format('n月j日');
    }

}
