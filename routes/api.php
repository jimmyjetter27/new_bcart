<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleController;
use App\Http\Middleware\ForceJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => [ForceJson::class]], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle']);
    Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('user-profile', [AuthController::class, 'userProfile']);
        Route::put('update-profile', [AuthController::class, 'updateProfile']);
        Route::put('update-password', [AuthController::class, 'updatePassword']);
    });
});


Route::get('clear-database', [\App\Http\Controllers\MigrationController::class, 'clearDatabase']);
Route::get('active', function () {
    return response()->json([
        'success' => true,
        'token' => '14|Aw8HbqJUIaFVFuYU0A2Xz3OqLRvWo5yu0Az64CJj9cd0130d',
        'data' => new \App\Http\Resources\UserResource(\App\Models\User::first())
    ]);
});

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Page not found'
    ], 404);
});

