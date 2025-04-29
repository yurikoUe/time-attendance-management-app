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
use Carbon\Carbon;




class AttendanceRequestController extends Controller
{
    //　申請内容を表示
    public function approve($attendance_correct_request)
    {
        // 申請データを取得（勤怠データ・詳細・休憩も一緒に）
        $attendanceRequest = AttendanceRequest::with(['attendance.breakTimes', 'breaks', 'details'])
            ->findOrFail($attendance_correct_request);

        // 関連する勤怠データを取得
        $attendance = $attendanceRequest->attendance;

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
            ? Carbon::parse($attendanceRequest->details->after_clock_in)->format('H:i')
            : ($attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : null);

        $clockOut = $attendanceRequest && $attendanceRequest->details && $attendanceRequest->details->after_clock_out
            ? Carbon::parse($attendanceRequest->details->after_clock_out)->format('H:i')
            : ($attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : null);


        return view('shared.attendance.admin-approve', compact('attendance', 'attendanceRequest','breaks', 'clockIn', 'clockOut'));


    }

    //スタッフの申請に基づき修正（承認→更新）
    public function approveUpdate(Request $request, AttendanceRequest $attendance_correct_request)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403, '管理者としてログインする必要があります');
        }

        \DB::transaction(function () use ($attendance_correct_request) {
            $attendance_correct_request->status_id = 2;
            $attendance_correct_request->save();

            $details = $attendance_correct_request->details;
            $attendance = $attendance_correct_request->attendance;

            if ($details) {
                $attendance->clock_in = $details->after_clock_in;
                $attendance->clock_out = $details->after_clock_out;
                $attendance->save();
            }

            // 元の break を退避
            $originalBreaks = $attendance->breakTimes()->get()->keyBy('id');

            // 一旦削除
            $attendance->breakTimes()->delete();

            // 変更のあった元BreakのIDを記録
            $updatedOriginalIds = [];

            foreach ($attendance_correct_request->breaks as $reqBreak) {
                $attendance->breakTimes()->create([
                    'break_start' => $reqBreak->after_break_start,
                    'break_end' => $reqBreak->after_break_end,
                ]);

                if (!empty($reqBreak->before_break_start) && !empty($reqBreak->after_break_start) &&
                    ($reqBreak->before_break_start !== $reqBreak->after_break_start ||
                    $reqBreak->before_break_end !== $reqBreak->after_break_end)) {
                    // 変更されたbreakと見なす（original IDは明示的でなくてもこの条件でOK）
                    foreach ($originalBreaks as $id => $ob) {
                        if ($ob->break_start->format('H:i') === date('H:i', strtotime($reqBreak->before_break_start)) &&
                            $ob->break_end->format('H:i') === date('H:i', strtotime($reqBreak->before_break_end))) {
                            $updatedOriginalIds[] = $id;
                            break;
                        }
                    }
                }
            }

            // 修正されなかった元休憩時間を再登録
            foreach ($originalBreaks as $id => $ob) {
                if (!in_array($id, $updatedOriginalIds)) {
                    $attendance->breakTimes()->create([
                        'break_start' => $ob->break_start,
                        'break_end' => $ob->break_end,
                    ]);
                }
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
            foreach ($request->breaks as $index => $break) {
                // 休憩時間が空でないことを確認
                if (empty($break['break_start']) || empty($break['break_end'])) {
                    continue; // 空の休憩時間は無視
                }

                $oldBreak = $attendance->breakTimes[$index] ?? null;
                
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
            }

            // コミットして変更を保存
            DB::commit();

            $attendance->refresh(); // DBの最新状態に更新

            return redirect()->route('attendance.show', ['id' => $attendance->id])
                ->with('success', '勤怠を修正しました');

        } catch (\Exception $e) {
            // 失敗した場合はロールバック
            DB::rollBack();
             \Log::error('Error occurred during attendance update: ' . $e->getMessage());
            return redirect()->back()->with('error', 'エラーが発生しました。再度お試しください。');
        }
    }


}