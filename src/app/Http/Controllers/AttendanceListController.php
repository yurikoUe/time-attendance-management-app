<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class AttendanceListController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakTimes', 'attendanceRequests')
            ->findOrFail($id);

        $isRequestPending = $attendance->attendanceRequests()->where('status_id', 1)->exists();

        $attendance->formatted_clock_in = optional($attendance->clock_in)->format('H:i');
        $attendance->formatted_clock_out = optional($attendance->clock_out)->format('H:i');

        foreach ($attendance->breakTimes as $break) {
            $break->formatted_break_start = optional($break->break_start)->format('H:i');
            $break->formatted_break_end = optional($break->break_end)->format('H:i');
        }

        $isAdmin = Auth::guard('admin')->check();
        $isUser = Auth::guard('web')->check();

        // 管理者の場合の編集モード判定（例：申請がない時だけ直接修正できる）
        $isEditMode = $isAdmin && !$isRequestPending;

        if ($isAdmin) {
            return view('admin.attendance.show', compact('attendance', 'isRequestPending', 'isEditMode'));
        } elseif ($isUser) {
            return view('user.attendance.show', compact('attendance', 'isRequestPending'));
        }

        abort(403, 'Unauthorized');
    }

}
