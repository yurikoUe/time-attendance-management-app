<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\UserStatus;



class AttendanceController extends Controller
{

    public function createAttendance()
    {

        $user = auth()->user();  // ログインユーザーを取得
        $userStatusName = $user->status->name;
        $today = \Carbon\Carbon::today();
        $todayFormatted = $today->format('Y年m月d日') . '(' . $today->locale('ja')->isoFormat('dd') . ')'; // 日本語の曜日

        return view('user.attendance.create', compact('todayFormatted', 'userStatusName'));
    }

    public function start(Request $request)
    {
        $user = auth()->user();
        $userStatus = $user->status;

        // 勤務外の状態でないときは出勤できない
        if($userStatus->name !== '勤務外'){
            return redirect()->back()->with('error', '出勤できる状態ではありません');
        }

        // 今日すでに出勤しているか確認
        $alreadyClockIn = Attendance::where('user_id', $user->id)
            ->where('work_date', Carbon::today()->toDateString())
            ->exists();

        // すでに出勤していたらエラー
        if ($alreadyClockIn) {
            return redirect()->back()->with('error', '今日はすでに出勤済みです');
        }

        //出勤時間を記録
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::now(),
            'clock_out' => null,
        ]);

        //ステータスを「勤務中」に更新
        $user->status_id = UserStatus::where('name', '出勤中')->first()->id;
        $user->save();

        return redirect()->route('attendance.create');
    }

    public function end(Request $request)
    {
        $user = auth()->user();
        $userStatus = $user->status;

        //勤務中でないときは退勤できない
        if($userStatus->name !== '出勤中'){
            return redirect()->back()->with('error', '退勤できる状態ではありません');
        }

        // 今日の勤務データを取得
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', Carbon::today()->toDateString())
            ->whereNull('clock_out')
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '今日はすでに退勤済みです');
        }

        //退勤時間を記録
        $attendance->clock_out = Carbon::now();
        $attendance->save();

        //ステータスを「退勤済」に更新
        $user->status_id = UserStatus::where('name', '退勤済')->first()->id;
        $user->save();

        return redirect()->route('attendance.create');
    }
}
