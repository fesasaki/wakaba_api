<?php

use App\Http\Controllers\Common\AuthController;
use App\Http\Controllers\Common\NotificationController;
use App\Http\Controllers\Common\RequisitionController;
use App\Http\Controllers\User\ImageController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

//Any user
Route::group([], function () {
    
    //Users
    Route::post('/auth/login',[AuthController::class, 'login']);
    Route::post('/auth/check',[AuthController::class, 'checkUser']);
    Route::post('/auth/recover',[AuthController::class, 'recoverPassword']);

    //Requisition
    Route::post('/requisition/update',[RequisitionController::class, 'update']);
    Route::post('/requisition',[RequisitionController::class, 'createRequisiton']);
    Route::get('/requisition',[RequisitionController::class, 'list']);

    Route::post('/auth/code',[NotificationController::class, 'checkCode']);
    Route::post('/auth/recode',[NotificationController::class, 'reCreate']);

});


// Auth Shared Routes
Route::group(['middleware' => ['auth']], function () {

    Route::delete('/auth/logout',                       [AuthController::class, 'logout']);
    
    Route::post('/users',[UserController::class, 'store']);
    Route::get('/user/{id}',[UserController::class, 'detail']);
    Route::get('/user-list',[UserController::class, 'index']);

    //Picture Service
    Route::get('/picture/user/{id}',[ImageController::class, 'userPicture']);

});