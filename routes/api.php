<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RecipientController;
use App\Http\Controllers\Api\TemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function(){
    
    Route::get('/user',function (){
        return Auth::user();
    });
    
    Route::post('/send-notification',[NotificationController::class,'SendNotification']);
    Route::post('/save-recipient',[RecipientController::class,'saveRecipient']);
    Route::post('/save-template',[TemplateController::class,'saveTemplate']);
    Route::post('/logout',[AuthController::class,'logout']);
});

Route::post('/login',[AuthController::class,'login']);
Route::post('/register',[AuthController::class,'register']);
