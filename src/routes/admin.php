<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AttendanceListController;
use App\Http\Controllers\Admin\StaffsController;
use App\Http\Controllers\Admin\AttendanceRequestController;

Route::middleware(['auth:admin', 'role:admin'])->group(function () {
   
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])
        ->name('admin.attendance-request.index');
    Route::redirect('/', '/admin/attendance/list');
    
    Route::get('/admin/attendance/list', [AttendanceListController::class, 'index'])->name('admin.attendance.index');
    Route::get('/admin/staff/list', [StaffsController::class, 'index'])->name('staff.list');
    Route::get('/admin/attendance/staff/{id}', [StaffsController::class, 'showAttendances'])->name('staff.attendance');
    Route::get('/attendance/staff/{id}/export/{month}', [StaffsController::class, 'exportCsv'])->name('attendance.exportCsv');    
});