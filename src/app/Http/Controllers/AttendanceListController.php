<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

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

        // 管理者
        if ($isAdmin) {
            if ($isRequestPending) {
                // 申請がある → 承認画面へリダイレクト
                $attendanceRequest = $attendance->attendanceRequests()->where('status_id', 1)->first();

                if (!$attendanceRequest) {
                    abort(404, 'Attendance request not found');
                }

                return redirect()->route('stamp_correction_request.approve', ['attendance_correct_request' => $attendanceRequest->id]);

            }
                // 編集モード → 管理者編集ビュー
                return view('shared.attendance.admin-edit-form', compact('attendance'));
        }

        // 一般ユーザー
        if ($isUser) {
            if ($isRequestPending) {
                // 修正申請がある場合
                $attendanceRequest = $attendance->attendanceRequests()
                    ->where('status_id', 1)
                    ->with(['breaks', 'details']) // 申請された休憩データも取得
                    ->first();

                $breaks = [];

                $originalBreaks = collect($attendance->breakTimes)->map(function ($break) {
                    return [
                        'start' => optional($break->break_start)->format('H:i'),
                        'end' => optional($break->break_end)->format('H:i'),
                    ];
                });

                // 申請された before 値を使って修正対象かどうかを判断
                $requestedBreaks = $attendanceRequest && $attendanceRequest->breaks->isNotEmpty()
                    ? $attendanceRequest->breaks
                    : collect();

                // 1. 修正されなかった元の休憩をそのまま表示
                $unmodifiedOriginals = $originalBreaks->filter(function ($original) use ($requestedBreaks) {
                    return !$requestedBreaks->contains(function ($request) use ($original) {
                        return
                            date('H:i', strtotime($request->before_break_start)) === $original['start'] &&
                            date('H:i', strtotime($request->before_break_end)) === $original['end'];
                    });
                });

                // 2. 修正後の休憩時間（after 値）を表示
                $modifiedBreaks = $requestedBreaks->map(function ($request) {
                    return [
                        'start' => $request->after_break_start ? date('H:i', strtotime($request->after_break_start)) : null,
                        'end' => $request->after_break_end ? date('H:i', strtotime($request->after_break_end)) : null,
                    ];
                });

                // 3. 結合して、フォーマット整える
                $mergedBreaks = $unmodifiedOriginals
                    ->merge($modifiedBreaks)
                    ->map(function ($break) {
                        return (object)[
                            'formatted_break_start' => $break['start'],
                            'formatted_break_end' => $break['end'],
                        ];
                    })
                    ->values()
                    ->toArray();

                $breaks = $mergedBreaks;

                
                // 出退勤時刻
                $clockIn = $attendanceRequest && $attendanceRequest->details && $attendanceRequest->details->after_clock_in
                    ? date('H:i', strtotime($attendanceRequest->details->after_clock_in))
                    : $attendance->formatted_clock_in;

                $clockOut = $attendanceRequest && $attendanceRequest->details && $attendanceRequest->details->after_clock_out
                    ? date('H:i', strtotime($attendanceRequest->details->after_clock_out))
                    : $attendance->formatted_clock_out;

                return view('shared.attendance.user-readonly', compact('attendance', 'breaks', 'clockIn', 'clockOut'));

            }

            // 編集モード → ユーザー編集ビュー
            return view('shared.attendance.user-edit-form', compact('attendance'));
        }

        abort(403, 'Unauthorized');
    }


    public function approve(AttendanceRequest $attendance_correct_request)
    {
        // 関連する勤怠データを取得（承認対象）
        $attendance = Attendance::with(['user', 'breakTimes', 'attendanceRequests'])
            ->findOrFail($attendance_correct_request->attendance_id);

        // 「承認待ち」状態かを判定（必要なら）
        $isRequestPending = $attendance->attendanceRequests()->where('status_id', 1)->exists();

        // 時刻をフォーマット
        $attendance->formatted_clock_in = optional($attendance->clock_in)->format('H:i');
        $attendance->formatted_clock_out = optional($attendance->clock_out)->format('H:i');
        foreach ($attendance->breakTimes as $break) {
            $break->formatted_break_start = optional($break->break_start)->format('H:i');
            $break->formatted_break_end = optional($break->break_end)->format('H:i');
        }

        // 管理者だけが承認できるように
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Unauthorized');
        }

        // 承認ビューに必要な変数を渡す
        return view('admin.attendance.approve', compact('attendance', 'attendance_correct_request', 'isRequestPending'));
    }
}
