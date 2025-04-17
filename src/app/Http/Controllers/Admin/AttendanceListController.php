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

    
}
