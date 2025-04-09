<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AttendanceRequestController extends Controller
{
    public function store(Request $request, $id)
    {
        $user = Auth::user();

        $attendance = Attendance::findOrFail($id);

        if ($attendance->attendanceRequests()->where('status_id', 1)->exists()) {
            return redirect()->back()->with('error', 'すでに申請済みです');
        }

        // バリデーションを追加して、修正理由が空でないことを確認
        $validated = $request->validate([
            'request_reason' => 'required|string|max:255', // 修正理由は必須
        ]);

        AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'request_reason' => $validated['request_reason'],
            'status_id' => 2, // 承認済みを示すステータスID
        ]);

        return redirect()->route('attendance-detail.show', ['id'=>$attendance->id]);
    }
}
