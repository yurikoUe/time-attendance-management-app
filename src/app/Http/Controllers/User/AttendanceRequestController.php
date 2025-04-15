<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestDetail;
use App\Models\AttendanceRequestBreak;
use App\Models\AttendanceRequestStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AttendanceRequestForm;

class AttendanceRequestController extends Controller
{
    public function store(AttendanceRequestForm $request, $id)
    {
        $user = Auth::user();

        $attendance = Attendance::findOrFail($id);

        // 既に申請中があれば弾く
        if ($attendance->attendanceRequests()->where('status_id', 1)->exists()) {
            return redirect()->back()->with('error', 'すでに申請済みです');
        }

        DB::beginTransaction();

        try {
            // 勤務修正申請作成
            $attendanceRequest = AttendanceRequest::create([
                'attendance_id' => $attendance->id,
                'request_reason' => $request->input('request_reason'),
                'status_id' => 1, // 承認待ち
            ]);

            // 出勤・退勤の修正チェック
            if ($attendance->clock_in->format('H:i') != $request->clock_in || $attendance->clock_out->format('H:i') != $request->clock_out) {
                AttendanceRequestDetail::create([
                    'attendance_request_id' => $attendanceRequest->id,
                    'before_clock_in' => $attendance->clock_in,
                    'before_clock_out' => $attendance->clock_out,
                    'after_clock_in' => $request->clock_in ?? $attendance->clock_in->format('H:i'),
                    'after_clock_out' => $request->clock_out ?? $attendance->clock_out->format('H:i'),
                ]);

            }

            // 休憩時間の修正・追加チェック
            foreach ($request->breaks as $index => $break) {

                // 両方入ってないとスキップ
                if (empty($break['break_start']) || empty($break['break_end'])) {
                    continue;
                }

                // 既存Break取得（あれば）
                $oldBreak = $attendance->breakTimes[$index] ?? null;

                // 既存の休憩時間をCarbon型に変換
                $oldBreakStart = $oldBreak ? $oldBreak->break_start->format('H:i') : null;
                $oldBreakEnd = $oldBreak ? $oldBreak->break_end->format('H:i') : null;

                // 既存がある → 差分チェック
                if ($oldBreakStart != $break['break_start'] || $oldBreakEnd != $break['break_end']) {
                    AttendanceRequestBreak::create([
                        'attendance_request_id' => $attendanceRequest->id,
                        'before_break_start' => $oldBreakStart,
                        'before_break_end' => $oldBreakEnd,
                        'after_break_start' => $break['break_start'],
                        'after_break_end' => $break['break_end'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('attendance-detail.show', ['id' => $attendance->id])
                ->with('success', '申請が完了しました');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'エラーが発生しました。再度お試しください。');
        }
    }

    public function index(Request $request)
    {
        $statusParam = $request->input('status', 'waiting'); //デフォルトは「承認待ち」
        $statusId = $statusParam === 'approved' ? 2 : 1; //1=承認待ち、 2=承認済み

        $requests = AttendanceRequest::with(['attendance.user', 'status'])
            ->where('status_id', $statusId)
            ->orderByDesc('created_at')
            ->get();

        return view('user.attendance_requests.index', [
            'requests' => $requests,
            'status' => $statusParam,
        ]);
    }
}
