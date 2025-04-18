<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;

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
}
