<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\AdminPointsController;

// Authentication Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'login'])->name('dologin');
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register')->middleware('alreadyLoggedIn');
Route::post('register', [RegisterController::class, 'register'])->name('doregister');
Route::post('logout', [LogoutController::class, 'logout'])->name('logout');
Route::get('dashboard',[LoginController::class,'dashboard'])->middleware('isLoggedIn');
Route::post('admin/update-points', [AdminPointsController::class, 'updateUserPoints'])->name('updatePoints');