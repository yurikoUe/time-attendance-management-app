<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\RegisteredUserController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\AttendanceListController;



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
    
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])->name('attendance-request.index');
});




