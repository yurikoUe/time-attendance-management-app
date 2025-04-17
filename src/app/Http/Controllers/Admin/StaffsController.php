<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class StaffsController extends Controller
{
    public function index()
    {
        $staff = User::all();

        return view('admin.staff.staff_list', compact('staff'));
    }
}
