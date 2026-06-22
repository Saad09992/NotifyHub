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
    Route::get('/notifications',[NotificationController::class,'listNotifications']);
    Route::get('/notifications/{id}',[NotificationController::class,'showNotification']);
    Route::get('/notifications/{id}/status',[NotificationController::class,'showNotificationStatus']);

    Route::post('/save-recipient',[RecipientController::class,'saveRecipient']);
    Route::put('/update-recipient/{recipient}',[RecipientController::class,'updateRecipient']);
    Route::get('/recipients',[RecipientController::class,'listRecipients']);
    Route::get('/recipients/{id}',[RecipientController::class,'showRecipient']);
    Route::delete('/recipients/{recipient}',[RecipientController::class,'deleteRecipient']);

    Route::post('/save-template',[TemplateController::class,'saveTemplate']);
    Route::put('/templates/{template}',[TemplateController::class,'updateTemplate']);
    Route::get('/templates',[TemplateController::class,'listTemplates']);
    Route::get('/templates/{id}',[TemplateController::class,'showTemplate']);
    Route::delete('/templates/{template}',[TemplateController::class,'deleteTemplate']);

    Route::post('/logout',[AuthController::class,'logout']);
});

Route::post('/login',[AuthController::class,'login']);
Route::post('/register',[AuthController::class,'register']);
