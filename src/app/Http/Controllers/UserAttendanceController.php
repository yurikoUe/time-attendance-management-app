<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserAttendanceController extends Controller
{
    public function index()
    {
        return view('user.attendance.index');
    }
}
