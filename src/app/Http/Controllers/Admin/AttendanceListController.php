<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        

        // 表示する日（デフォルトは今日）
        if ($request->has('date')){
            $currentDate = Carbon::parse($request->input('date'));
        } else {
            $currentDate = Carbon::today();
        }

        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('work_date', $currentDate->toDateString())
            ->get();
        
        return view('admin.attendance.index', [
            'currentDate' => $currentDate,
            'attendances' => $attendances,
            'prevDate' => $currentDate->copy()->subDay()->format('Y-m-d'),
            'nextDate' => $currentDate->copy()->addDay()->format('Y-m-d'),
        ]);
        
    }

    // public function show($id)
    // {

    //     $attendance = Attendance::with('user', 'breakTimes', 'attendanceRequests')
    //     ->findOrFail($id);

    //     // 申請がすでに存在していて、ステータスが「承認待ち」の場合は true
    //     $isRequestPending = $attendance->attendanceRequests()->where('status_id', 1)->exists();

    //     // 出勤・退勤時刻を整形
    //     $attendance->formatted_clock_in = optional($attendance->clock_in)->format('H:i');
    //     $attendance->formatted_clock_out = optional($attendance->clock_out)->format('H:i');

    //     // 休憩時刻を整形
    //     foreach ($attendance->breakTimes as $break){
    //         $break->formatted_break_start = optional($break->break_start)->format('H:i');
    //         $break->formatted_break_end = optional($break->break_end)->format('H:i');
    //     }

    //     return view('user.attendance.show', compact('attendance', 'isRequestPending'));
    // }
}
