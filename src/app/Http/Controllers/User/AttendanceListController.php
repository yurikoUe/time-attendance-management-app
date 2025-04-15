<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\UserStatus;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 表示する月（指定がなければ今月）を取得し、Carbonインスタンスに変換
        $month = $request->input('month', now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->orderBy('work_date', 'asc')
            ->get();

        return view('user.attendance.index', [
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
            'prevMonth' => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentMonth->copy()->addMonth()->format('Y-m'),
        ]);
    }

    public function show($id)
    {

        $attendance = Attendance::with('user', 'breakTimes', 'attendanceRequests')
        ->findOrFail($id);

        // 申請がすでに存在していて、ステータスが「承認待ち」の場合は true
        $isRequestPending = $attendance->attendanceRequests()->where('status_id', 1)->exists();

        // 出勤・退勤時刻を整形
        $attendance->formatted_clock_in = optional($attendance->clock_in)->format('H:i');
        $attendance->formatted_clock_out = optional($attendance->clock_out)->format('H:i');

        // 休憩時刻を整形
        foreach ($attendance->breakTimes as $break){
            $break->formatted_break_start = optional($break->break_start)->format('H:i');
            $break->formatted_break_end = optional($break->break_end)->format('H:i');
        }

        return view('user.attendance.show', compact('attendance', 'isRequestPending'));
    }
}
