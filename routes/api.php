<?php

use App\Http\Controllers\Common\AuthController;
use App\Http\Controllers\Common\NotificationController;
use App\Http\Controllers\Common\RequisitionController;
use App\Http\Controllers\User\CategoryController;
use App\Http\Controllers\User\EventController;
use App\Http\Controllers\User\ImageController;
use App\Http\Controllers\User\PositionController;
use App\Http\Controllers\User\PublicationController;
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
    
    Route::post('/user',[UserController::class, 'store']);
    Route::get('/user/{id}',[UserController::class, 'detail']);
    Route::get('/user-list',[UserController::class, 'index']);
    Route::get('/user-credential',[UserController::class, 'credential']);
    Route::post('/user-photo',[ImageController::class, 'storePhoto']);
    Route::post('/user-admin',[UserController::class, 'setAdmin']);
    Route::put('/user/{id}',[UserController::class, 'update']);
    Route::post('/user-inactive',[UserController::class, 'inactive']);

    //Picture Service
    Route::get('/picture/user/{id}',[ImageController::class, 'userPicture']);

    //Position
    Route::post('/position',[PositionController::class, 'store']);
    Route::get('/position',[PositionController::class, 'index']);
    Route::get('/position/{id}',[PositionController::class, 'detail']);
    Route::delete('/position/{id}',[PositionController::class, 'delete']);
    Route::post('/position-set',[PositionController::class, 'setPosition']);
    Route::post('/position-update/{id}',[PositionController::class, 'update']);

    //Category
    Route::post('/category',[CategoryController::class, 'store']);
    Route::get('/category',[CategoryController::class, 'index']);
    Route::delete('/category/{id}',[CategoryController::class, 'delete']);
    Route::post('/category-set',[CategoryController::class, 'setPosition']);
    Route::post('/category-unset',[CategoryController::class, 'unsetPosition']);

    //Event
    Route::post('/event',[EventController::class, 'store']); 
    Route::get('/event',[EventController::class, 'index']);
    Route::get('/event/{id}',[EventController::class, 'detail']);
    Route::post('/event-subscribe',[EventController::class, 'subscribe']);
    Route::post('/event-cancel-subscribe',[EventController::class, 'cancelSubscribe']);
    Route::delete('/event/{id}',[EventController::class, 'delete']);

    //Publication
    Route::post('/publication',[PublicationController::class, 'store']);
    Route::get('/publication',[PublicationController::class, 'index']);
    Route::post('/publication-reaction',[PublicationController::class, 'setReaction']);
    Route::get('/publication-reaction/{id}',[PublicationController::class, 'getReaction']);
    Route::delete('/publication/{id}',[PublicationController::class, 'delete']);
});