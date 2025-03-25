<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\RegisteredUserController;

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

Route::middleware('auth')->group(function () {
    Route::get('/', [AttendanceController::class, 'index']);
});