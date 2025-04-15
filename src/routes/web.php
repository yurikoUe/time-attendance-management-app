<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\RegisteredUserController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\User\AttendanceListController;
use App\Http\Controllers\User\AttendanceRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 管理者ルート
Route::get('/admin/login', function(){
    return view('admin.login');
});

Route::middleware(['auth:admin'])->group(function () {
    // 管理者ルート（verified は不要）
});

// ユーザールート
Route::get('/register', [RegisteredUserController::class, 'create']);
Route::get('/email/verify', [VerificationController::class, 'notice'])
    ->name('verification.notice');
Route::post('/email/resend', [VerificationController::class, 'resend'])
    ->name('verification.resend');

Route::middleware(['auth:web', 'verified'])->group(function () {
Route::redirect('/', '/attendance');

    // 勤怠登録（出勤、退勤）
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendances.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendances.end');
    Route::get('/attendance', [AttendanceController::class, 'createAttendance'])
    ->name('attendance.create');

    // 勤怠登録（休憩）
    Route::post('/breaks/start', [BreakController::class, 'start'])->name('breaks.start');
    Route::post('/breaks/end', [BreakController::class, 'end'])->name('breaks.end');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('attendance.index');

    // 勤怠詳細表示
    Route::get('/attendance/{id}', [AttendanceListController::class, 'show'])->name('attendance-detail.show');

    // 修正申請処理
    Route::post('/attendance/{id}/request', [AttendanceRequestController::class, 'store'])->name('attendance-request.store');

    Route::get('/attendance-requests', [AttendanceRequestController::class, 'index'])->name('attendance-request.index');
});