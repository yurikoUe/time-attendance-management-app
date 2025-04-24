<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AttendanceListController;
use App\Http\Controllers\Admin\StaffsController;
use App\Http\Controllers\Admin\AttendanceRequestController;

Route::middleware(['auth:admin', 'role:admin'])->group(function () {
    Route::redirect('/', '/admin/attendance/list');
    
    Route::get('/admin/attendance/list', [AttendanceListController::class, 'index'])->name('admin.attendance.index');
    Route::get('/admin/staff/list', [StaffsController::class, 'index'])->name('staff.list');
    Route::get('/admin/attendance/staff/{id}', [StaffsController::class, 'showAttendances'])->name('staff.attendance');
    Route::get('/attendance/staff/{id}/export/{month}', [StaffsController::class, 'exportCsv'])->name('attendance.exportCsv');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceRequestController::class, 'approve'])
    ->name('stamp_correction_request.approve');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AttendanceRequestController::class, 'approveUpdate'])
    ->name('stamp_correction_request.update');

    // 管理者が勤怠を直接修正する（PUT）
    Route::put('/admin/attendance/{attendance}', [AttendanceRequestController::class, 'update'])->name('admin.attendance.update');


});