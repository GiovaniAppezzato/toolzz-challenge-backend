<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{TestConnectionController,AuthController,ChatController,UserController};

Route::get('/test-connection', TestConnectionController::class);

Route::post('/sign-in', [AuthController::class, 'signIn']);
Route::post('/sign-up', [AuthController::class, 'signUp']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/show-me', [AuthController::class, 'show']);
    Route::delete('/sign-out', [AuthController::class, 'signOut']);

    Route::apiResource('/users', UserController::class);
    Route::get('/get-users-for-conversation', [ChatController::class, 'index']);
    Route::get('/get-messages', [ChatController::class, 'show']);
    Route::post('/send-message', [ChatController::class, 'store']);
    Route::post('/read-messages', [ChatController::class, 'setMessagesAsRead']);
});
