<?php

use App\Http\Controllers\Common\AuthController;
use App\Http\Controllers\Common\NotificationController;
use App\Http\Controllers\Common\RequisitionController;
use App\Http\Controllers\Common\UserController;
use Illuminate\Support\Facades\Route;

//Any user
Route::group([], function () {
    
    //Users
    Route::post('/auth/login',[AuthController::class, 'login']);
    Route::post('/auth/check',[AuthController::class, 'checkUser']);
    Route::get('/users',[UserController::class, 'index']);
    Route::post('/users',[UserController::class, 'store']);

    //Requisition
    Route::post('/requisition/update',[RequisitionController::class, 'update']);
    Route::post('/requisition',[RequisitionController::class, 'createRequisiton']);
    Route::get('/requisition',[RequisitionController::class, 'list']);

    Route::post('/auth/code',[NotificationController::class, 'checkCode']);

});
