<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AttendanceController as UserAttendanceController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\User\RegisteredUserController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\User\AttendanceListController as UserAttendanceListController;
use App\Http\Controllers\Admin\AttendanceListController as AdminAttendanceListController;;
use App\Http\Controllers\User\AttendanceRequestController;
use App\Http\Controllers\Admin\StaffsController;


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
})->middleware('guest:admin')->name('admin.login');

// 管理者認証ルート
Route::middleware(['auth:admin'])->group(function () {
    Route::redirect('/', '/admin/attendance/list');
    Route::get('/admin/attendance/list', [AdminAttendanceListController::class, 'index'])->name('admin.attendance.index');
    Route::get('/admin/staff/list', [StaffsController::class, 'index'])->name('staff.list');
});

//ユーザーと管理者の両方とも認証 (カスタムミドルウェア)
Route::middleware(['admin.or.user'])->group(function () {
    Route::get('/attendance/{id}', [UserAttendanceListController::class, 'show'])->name('attendance-detail.show');
    Route::post('/attendance/{id}', [AttendanceRequestController::class, 'store'])->name('attendance-request.store');
});

// ユーザールート
Route::get('/register', [RegisteredUserController::class, 'create']);
Route::get('/email/verify', [VerificationController::class, 'notice'])
    ->name('verification.notice');
Route::post('/email/resend', [VerificationController::class, 'resend'])
    ->name('verification.resend');


// ユーザー認証ルート
Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::redirect('/', '/attendance'); //ログアウト後のリダイレクト先を変更

    // 勤怠登録（出勤、退勤）
    Route::post('/attendance/start', [UserAttendanceController::class, 'start'])->name('attendances.start');
    Route::post('/attendance/end', [UserAttendanceController::class, 'end'])->name('attendances.end');
    Route::get('/attendance', [UserAttendanceController::class, 'createAttendance'])
    ->name('attendance.create');

    // 勤怠登録（休憩）
    Route::post('/breaks/start', [BreakController::class, 'start'])->name('breaks.start');
    Route::post('/breaks/end', [BreakController::class, 'end'])->name('breaks.end');

    // 勤怠一覧
    Route::get('/attendance/list', [UserAttendanceListController::class, 'index'])->name('attendance.index');

    // 勤怠詳細表示
    // Route::get('/attendance/{id}', [UserAttendanceListController::class, 'show'])->name('attendance-detail.show');

    // 修正申請処理
    

    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])->name('attendance-request.index');
});