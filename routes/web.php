<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\AdminPointsController;
use App\Http\Controllers\AdminMoneyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReceiptTransferController;

// Authentication Routes
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'login'])->name('dologin');
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register'])->name('doregister');
Route::post('logout', [LogoutController::class, 'logout'])->name('logout');
Route::get('/transfer-receipt/{id}', [ReceiptTransferController::class, 'showTransferReceipt'])->name('transfer.receipt');

// Protected Routes
Route::middleware(['isLoggedIn'])->group(function () {
    Route::get('dashboard', [LoginController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard_money', function () {
        return view('dashboard_money');
    })->name('dashboard_money');

    // Admin Points Routes
    Route::post('admin/update-points', [AdminPointsController::class, 'updateUserPoints'])->name('updatePoints');

    // Admin Money Routes
    Route::post('/admin/fetch-user', [AdminMoneyController::class, 'fetchUser']);
    Route::post('/admin/transfer-money', [AdminMoneyController::class, 'transferMoney']);
});
