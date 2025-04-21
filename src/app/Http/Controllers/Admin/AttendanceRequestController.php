<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;


class AttendanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $statusParam = $request->input('status', 'waiting'); //デフォルトは「承認待ち」
        $statusId = $statusParam === 'approved' ? 2 : 1; //1=承認待ち、 2=承認済み

        $requests = AttendanceRequest::with(['attendance.user', 'status'])
            ->where('status_id', $statusId)
            ->orderByDesc('created_at')
            ->get();

        return view('user.request.index', [
            'requests' => $requests,
            'status' => $statusParam,
        ]);
    }
}
