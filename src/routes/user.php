<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\AttendanceListController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\User\AttendanceRequestController;

// ユーザールート
Route::middleware(['auth:web', 'verified', 'role:user'])->group(function () {
    Route::redirect('/', '/attendance'); //ログアウト後のリダイレクト先を変更

    // 勤怠登録（出勤、退勤）
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendances.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendances.end');
    Route::get('/attendance', [AttendanceController::class, 'createAttendance'])->name('attendance.create');

    // 勤怠登録（休憩）
    Route::post('/breaks/start', [BreakController::class, 'start'])->name('breaks.start');
    Route::post('/breaks/end', [BreakController::class, 'end'])->name('breaks.end');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('attendance.index');

 

    
});