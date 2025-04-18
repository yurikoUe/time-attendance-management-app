<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffsController extends Controller
{
    public function index()
    {
        $staff = User::all();

        return view('admin.staff.staff_list', compact('staff'));
    }

    public function showAttendances(Request $request, $id)
    {

        //表示する月（デフォルトは今月）
        if ($request->has('month')){
            $currentMonth = Carbon::parse($request->input('month'));
        } else {
            $currentMonth = Carbon::today();
        }

        $attendances = Attendance::with('user', 'breakTimes')
            ->where('user_id', $id) //ユーザーIDで勤怠を絞る
            ->whereBetween('work_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->orderBy('work_date', 'asc')
            ->get();

        $user = User::findOrFail($id);

        return view('admin.staff.staff_attendance',[
            'attendances' => $attendances,
            'currentMonth' =>$currentMonth,
            'prevMonth' => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentMonth->copy()->addMonth()->format('Y-m'),
            'user' => $user,
        ]);
    }

    public function exportCsv($id, $month)
    {
        $user = User::findOrFail($id);

        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_' . $month . '.csv"',
        ];

        $callback = function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->formatted_work_date,
                    optional($attendance->clock_in)->format('H:i'),
                    optional($attendance->clock_out)->format('H:i'),
                    $attendance->total_break_time,
                    $attendance->total_work_time,
                ]);
            }

            fclose($handle);
        };

    return new StreamedResponse($callback, 200, $headers);
    }
}
