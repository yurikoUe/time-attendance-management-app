<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\RegisteredUserController;
use App\Http\Controllers\VerificationController;

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

Route::get('/register', [RegisteredUserController::class, 'create']);
Route::get('/email/verify', [VerificationController::class, 'notice'])
    ->name('verification.notice');
Route::post('/email/resend', [VerificationController::class, 'resend'])
    ->name('verification.resend');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [AttendanceController::class, 'index']);
    Route::get('/attendance', [AttendanceController::class, 'create']);
});