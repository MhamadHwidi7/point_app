<?php

use App\Http\Controllers\UserInformationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserSignupController;
use App\Http\Controllers\UserLoginController;
use App\Http\Controllers\PointTransactionController;
use App\Http\Controllers\FirstCharNameController;
use App\Http\Controllers\VerifyCodeController;
use App\Http\Controllers\RajhiTransferOneTimeController;
use App\Http\Controllers\LocalTransferOneTimeController;
use App\Http\Controllers\TransactionDetailsController;

// User Authentication Routes
Route::post('/register', [UserSignupController::class, 'register']);
Route::post('/login', [UserLoginController::class, 'login']);
Route::post('/verify-code', [VerifyCodeController::class, 'verifyCode']);

// Point Transaction Routes
Route::post('/transfer-points', [PointTransactionController::class, 'transferPoints']);
Route::get('/user/points', [PointTransactionController::class, 'getUserPoints']);

// User Information Routes
Route::get('/user-info', [UserInformationController::class, 'getUserInfo']);
Route::get('/user-first-char', [FirstCharNameController::class, 'getFirstCharacterOfName']);
//
Route::post('/transfer-onetime', [RajhiTransferOneTimeController::class, 'transfer']);
Route::get('/check-receiver-account', [RajhiTransferOneTimeController::class, 'checkReceiverAccount']);
Route::get('/transaction-details', [RajhiTransferOneTimeController::class, 'getTransactionDetails']);


Route::post('/local-transfer', [LocalTransferOneTimeController::class, 'transfer']);
Route::get('/check-receiver-card', [LocalTransferOneTimeController::class, 'checkReceiverAccount']);
Route::get('/local-transaction-details', [LocalTransferOneTimeController::class, 'getLocalTransactionDetails']);
Route::get('/show-transaction-details', [TransactionDetailsController::class, 'showTransactionDetails']);
