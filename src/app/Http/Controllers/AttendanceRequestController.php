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
}
