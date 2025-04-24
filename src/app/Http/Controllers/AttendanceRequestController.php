<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use App\Http\Requests\AttendanceRequestForm;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceRequestBreak;



class AttendanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $statusParam = $request->input('status', 'waiting'); // デフォルトは「承認待ち」
        $statusId = $statusParam === 'approved' ? 2 : 1; // 1 = 承認待ち, 2 = 承認済み

        // 管理者かユーザーかで取得するデータを変える
        if (auth('admin')->check()) {
            // 管理者：全てのリクエストを取得
            $requests = AttendanceRequest::with(['attendance.user', 'status'])
                ->where('status_id', $statusId)
                ->orderByDesc('created_at')
                ->get();

            return view('admin.request.index', [
                'requests' => $requests,
                'status' => $statusParam,
            ]);

        } elseif (auth('web')->check()) {
            // ユーザー：自分の勤怠だけ
            $requests = AttendanceRequest::with(['attendance.user', 'status'])
                ->where('status_id', $statusId)
                ->whereHas('attendance', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->orderByDesc('created_at')
                ->get();

            return view('user.request.index', [
                'requests' => $requests,
                'status' => $statusParam,
            ]);
        }

        // どちらでもない（未ログイン）なら403
        abort(403, 'Unauthorized');
    }

    // public function store(AttendanceRequestForm $request, $id)
    // {
    //     $user = Auth::user();

    //     $attendance = Attendance::findOrFail($id);

    //     // 既に申請中があれば弾く
    //     if ($attendance->attendanceRequests()->where('status_id', 1)->exists()) {
    //         return redirect()->back()->with('error', 'すでに申請済みです');
    //     }

    //     DB::beginTransaction();

    //     try {
    //         // 勤務修正申請作成
    //         $attendanceRequest = AttendanceRequest::create([
    //             'attendance_id' => $attendance->id,
    //             'request_reason' => $request->input('request_reason'),
    //             'status_id' => 1, // 承認待ち
    //         ]);

    //         // 出勤・退勤の修正チェック
    //         if ($attendance->clock_in->format('H:i') != $request->clock_in || $attendance->clock_out->format('H:i') != $request->clock_out) {
    //             AttendanceRequestDetail::create([
    //                 'attendance_request_id' => $attendanceRequest->id,
    //                 'before_clock_in' => $attendance->clock_in,
    //                 'before_clock_out' => $attendance->clock_out,
    //                 'after_clock_in' => $request->clock_in ?? $attendance->clock_in->format('H:i'),
    //                 'after_clock_out' => $request->clock_out ?? $attendance->clock_out->format('H:i'),
    //             ]);

    //         }

    //         // 休憩時間の修正・追加チェック
    //         foreach ($request->breaks as $index => $break) {

    //             // 両方入ってないとスキップ
    //             if (empty($break['break_start']) || empty($break['break_end'])) {
    //                 continue;
    //             }

    //             // 既存Break取得（あれば）
    //             $oldBreak = $attendance->breakTimes[$index] ?? null;

    //             // 既存の休憩時間をCarbon型に変換
    //             $oldBreakStart = $oldBreak ? $oldBreak->break_start->format('H:i') : null;
    //             $oldBreakEnd = $oldBreak ? $oldBreak->break_end->format('H:i') : null;

    //             // 既存がある → 差分チェック
    //             if ($oldBreakStart != $break['break_start'] || $oldBreakEnd != $break['break_end']) {
    //                 AttendanceRequestBreak::create([
    //                     'attendance_request_id' => $attendanceRequest->id,
    //                     'before_break_start' => $oldBreakStart,
    //                     'before_break_end' => $oldBreakEnd,
    //                     'after_break_start' => $break['break_start'],
    //                     'after_break_end' => $break['break_end'],
    //                 ]);
    //             }
    //         }

    //         DB::commit();

    //         return redirect()->route('attendance-detail.show', ['id' => $attendance->id])
    //             ->with('success', '申請が完了しました');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()->back()->with('error', 'エラーが発生しました。再度お試しください。');
    //     }
    // }

}
