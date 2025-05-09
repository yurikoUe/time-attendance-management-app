<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\UserStatus;
use Illuminate\Support\Facades\Auth;


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

}
