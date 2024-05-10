<?php

use App\Http\Controllers\UserInformationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserSignupController;
use App\Http\Controllers\UserLoginController;
use App\Http\Controllers\PointTransactionController;
use App\Http\Controllers\FirstCharNameController;

//Route::get('/user', function (Request $request) {
  //  return $request->user();
//})->middleware('auth:sanctum');

Route::post('/login', [UserLoginController::class, 'login']);
//Route::prefix('/user-auth')->group(function () {
  //  Route::post('register', [UserSignupController::class, 'register']);
//});


    Route::post('register', [UserSignupController::class, 'register']);
    Route::post('login', [UserLoginController::class, 'login']);
    Route::post('transfer-points', [PointTransactionController::class, 'transferPoints']);
    Route::get('user/points', [PointTransactionController::class, 'getUserPoints']);


    Route::get('/user-info', [UserInformationController::class, 'getUserInfo']);
    Route::get('/user-first-char', [FirstCharNameController::class, 'getFirstCharacterOfName']);