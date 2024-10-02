<?php

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

require __DIR__.'/auth.php';
