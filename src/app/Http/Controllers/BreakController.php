<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\UserStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BreakController extends Controller
{
    //休憩開始
    public function start(Request $request)
    {
        $user =auth()->user();
        
        //今日の出勤データを取得
        $attendance = Attendance::where('user_id', $user->id)
                                ->whereDate('work_date', Carbon::today())
                                ->whereNull('clock_out')
                                ->first();

        if (!$attendance){
            return redirect()->back()->with('error', '出勤してません');
        }

        //休憩開始
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now(),
            'break_end' => null,
        ]);

        //ステータス更新（休憩中）
        $user->status_id = UserStatus::where('name', '休憩中')->first()->id;
        $user->save();
        
        return redirect()->back();
    }

    //休憩終了
    public function end(Request $request)
    {
        $user =auth()->user();

        $attendance = Attendance::where('user_id', $user->id)
                                ->whereDate('work_date', Carbon::today())
                                ->whereNull('clock_out')
                                ->first();
        
        if (!$attendance){
            return redirect()->back()->with('error', '出勤していません');
        }

        //最後に開始した休憩レコードを取得（終了時間がNUllのもの）
        $break = $attendance->breakTimes()
                            ->whereNull('break_end')
                            ->latest('break_start')
                            ->first();
        if (!$break) {
            return redirect()->back()->with('error', '休憩中ではありません');
        }

        //休憩終了
        $break->break_end = Carbon::now();
        $break->save();

        //ステータス更新（勤務中）
        $user->status_id = UserStatus::where('name', '出勤中')->first()->id;
        $user->save();

        return redirect()->back();
    }

}
