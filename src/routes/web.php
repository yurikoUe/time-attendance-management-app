<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AttendanceController as UserAttendanceController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\User\RegisteredUserController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\User\AttendanceListController as UserAttendanceListController;
use App\Http\Controllers\Admin\AttendanceListController as AdminAttendanceListController;;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\Admin\StaffsController;
use App\Http\Controllers\AttendanceListController;


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


// 管理者認証前
Route::get('/admin/login', function(){
    return view('admin.login');
})->middleware('guest:admin')->name('admin.login');

// ユーザー認証前
Route::get('/register', [RegisteredUserController::class, 'create']);
Route::get('/email/verify', [VerificationController::class, 'notice'])
    ->name('verification.notice');
Route::post('/email/resend', [VerificationController::class, 'resend'])
    ->name('verification.resend');

// ユーザーもしくは管理者なら認証
Route::middleware(['admin.or.user'])->group(function () {
    Route::get('/attendance/{id}', [AttendanceListController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/{id}', [AttendanceRequestController::class, 'store'])->name('attendance-request.store');
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])->name('attendance-request.index');
});




