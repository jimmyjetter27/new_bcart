<?php

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

//Route::get('/email/verify', function () {
//    return view('auth.verify-email');
//})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return view('verify_email');
})->middleware(['auth', 'signed'])->name('verification.verify');

require __DIR__.'/auth.php';
