<?php

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\UserResource;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return 'Bcart Backend';
//    return ['Laravel' => app()->version()];
});

//Route::view('pass-reset', 'pass_reset');
Route::get('pass-reset/{token}', function ($token) {
    return [
        'message' => 'route worked',
        'token' => $token
    ];
});

Route::view('flutter-webhook', 'paystack_hook');

Route::get('/auth/callback', [\App\Http\Controllers\TestController::class, 'handleCallback']);

//Route::get('/email/verify', function () {
//    return view('auth.verify-email');
//})->middleware('auth')->name('verification.notice');


require __DIR__.'/auth.php';
