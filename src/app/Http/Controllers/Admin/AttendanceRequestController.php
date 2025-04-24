<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceRequestForm;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceRequestBreak;
use App\Models\AttendanceRequestDetail;





class AttendanceRequestController extends Controller
{
    //　申請内容を表示
    public function approve($attendance_correct_request)
    {
        // attendance_requestテーブルから指定されたIDの申請を取得
        $attendanceRequest = AttendanceRequest::with('attendance', 'attendance.attendanceRequests')
            ->findOrFail($attendance_correct_request);

        // 関連するAttendanceモデルを取得
        $attendance = $attendanceRequest->attendance;

        // 出勤・退勤時刻や休憩などのフォーマット処理
        $attendance->formatted_clock_in = optional($attendance->clock_in)->format('H:i');
        $attendance->formatted_clock_out = optional($attendance->clock_out)->format('H:i');
        $attendance->formatted_work_date_year = optional($attendance->work_date)->format('Y年');
        $attendance->formatted_work_date_month_day = optional($attendance->work_date)->format('n月j日');

        foreach ($attendance->breakTimes as $break) {
            $break->formatted_break_start = optional($break->break_start)->format('H:i');
            $break->formatted_break_end = optional($break->break_end)->format('H:i');
        }

        return view('shared.attendance.admin-approve', compact('attendance', 'attendanceRequest'));
    }

    //スタッフの申請に基づき修正（承認→更新）
    public function approveUpdate(Request $request, AttendanceRequest $attendance_correct_request)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403, '管理者としてログインする必要があります');
        }

        // トランザクションで一括更新
        \DB::transaction(function () use ($attendance_correct_request) {
            // ステータスを「修正済み」に更新
            $attendance_correct_request->status_id = 2;
            $attendance_correct_request->save();

            // 勤怠データの更新（details が変更されない場合はスキップ）
            $details = $attendance_correct_request->details;
            $attendance = $attendance_correct_request->attendance;

            // details が変更されていない場合、更新処理をスキップ
            if ($details) {
                $attendance->clock_in = $details->after_clock_in;
                $attendance->clock_out = $details->after_clock_out;
                $attendance->save();
            }

            // 休憩データ更新（breaks が変更されていない場合はスキップ）
            $attendance->breakTimes()->delete();

            foreach ($attendance_correct_request->breaks as $reqBreak) {
                $attendance->breakTimes()->create([
                    'break_start' => $reqBreak->after_break_start,
                    'break_end' => $reqBreak->after_break_end,
                ]);
            }
        });

        return redirect()->route('attendance.show', ['id' => $attendance_correct_request->attendance_id])
            ->with('success', '修正を承認しました');
    }

    //管理者が直接修正
    public function update(AttendanceRequestForm $request, Attendance $attendance)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403, '管理者としてログインする必要があります');
        }

        // トランザクション開始
        DB::beginTransaction();

        try {

            // 勤怠修正申請を作成
            $attendanceRequest = AttendanceRequest::create([
                'attendance_id' => $attendance->id,
                'request_reason' => $request->input('request_reason'),
                'status_id' => 2, // 修正済み
                'is_admin_created' => true,
            ]);

            // 出勤・退勤の修正（前後の時間が異なる場合のみ）
            if ($attendance->clock_in->format('H:i') != $request->clock_in || $attendance->clock_out->format('H:i') != $request->clock_out) {
                AttendanceRequestDetail::create([
                    'attendance_request_id' => $attendanceRequest->id,
                    'before_clock_in' => $attendance->clock_in,
                    'before_clock_out' => $attendance->clock_out,
                    'after_clock_in' => $request->clock_in,
                    'after_clock_out' => $request->clock_out,
                ]);
            }

            // 休憩時間の修正・追加チェック
            foreach ($request->breaks as $index => $break) {

                // 両方の休憩時間がない場合はスキップ
                if (empty($break['break_start']) || empty($break['break_end'])) {
                    continue;
                }

                // 既存の休憩時間（もしあれば）を取得
                $oldBreak = $attendance->breakTimes[$index] ?? null;

                // 既存の休憩時間をCarbon型に変換
                $oldBreakStart = $oldBreak ? $oldBreak->break_start->format('H:i') : null;
                $oldBreakEnd = $oldBreak ? $oldBreak->break_end->format('H:i') : null;

                // 既存のデータと異なる場合にのみ保存
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

            // 勤怠データの更新
            $attendance->clock_in = $request->input('clock_in');
            $attendance->clock_out = $request->input('clock_out');
            $attendance->save();

            // === ここから break_times テーブル自体の更新/追加 ===
            if ($oldBreak) {
                // 既存の休憩を更新
                $oldBreak->update([
                    'break_start' => $break['break_start'],
                    'break_end' => $break['break_end'],
                ]);
            } else {
                // 新しい休憩時間として追加
                $attendance->breakTimes()->create([
                    'break_start' => $break['break_start'],
                    'break_end' => $break['break_end'],
                ]);
            }


            // コミットして変更を保存
            DB::commit();

            $attendance->refresh(); // DBの最新状態に更新

            return redirect()->route('attendance.show', ['id' => $attendance->id])
                ->with('success', '勤怠を修正しました');
        } catch (\Exception $e) {
            // 失敗した場合はロールバック
            DB::rollBack();
            return redirect()->back()->with('error', 'エラーが発生しました。再度お試しください。');
        }
    }


}
