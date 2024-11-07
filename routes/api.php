<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleController;
use App\Http\Middleware\ForceJson;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

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

    Route::get('/email-verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['auth:sanctum', 'signed'])
        ->name('verification.verify');


    Route::get('email/resend', [AuthController::class, 'resendVerificationEmail'])
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name('verification.send');


    Route::group(['middleware' => ['auth:sanctum', 'verified']], function () {
        Route::get('user-profile', [AuthController::class, 'userProfile'])->withoutMiddleware('verified');
        Route::put('update-profile', [AuthController::class, 'updateProfile']);
        Route::put('update-creative-details', [AuthController::class, 'updateCreativeDetails']);
        Route::put('update-password', [AuthController::class, 'updatePassword']);
        Route::put('update-avatar', [AuthController::class, 'updateAvatar']);

        Route::put('approve-photo', [\App\Http\Controllers\PhotoController::class, 'approvePhoto']);

        Route::post('buy-photos', [\App\Http\Controllers\OrderController::class, 'buyPhotos']);
        Route::post('hire-creative', [\App\Http\Controllers\HiringController::class, 'store']);
        Route::post('suggest-upload', [\App\Http\Controllers\SuggestUploadController::class, 'store']);
    });

    Route::get('search-user', [\App\Http\Controllers\UserController::class, 'search']);
    Route::get('search-creative', [\App\Http\Controllers\CreativeController::class, 'search']);
    Route::get('search-photo', [\App\Http\Controllers\PhotoController::class, 'search']);

    Route::get('featured-creative', [\App\Http\Controllers\CreativeController::class, 'featuredCreative']);
    Route::get('featured-creatives', [\App\Http\Controllers\CreativeController::class, 'featuredCreatives']);

    Route::get('featured-creative-categories', [\App\Http\Controllers\CreativeCategoryController::class, 'featuredCreativeCategories']);

    Route::get('related-images/{photo}', [\App\Http\Controllers\PhotoController::class, 'relatedImages']);

    Route::apiResources([
        'creatives' => \App\Http\Controllers\CreativeController::class,
        'creative-categories' => \App\Http\Controllers\CreativeCategoryController::class,
        'photo-categories' => \App\Http\Controllers\PhotoCategoryController::class,
        'photos' => \App\Http\Controllers\PhotoController::class
    ]);

    Route::get('paystack-callback', []);

});


Route::get('clear-database', [\App\Http\Controllers\MigrationController::class, 'clearDatabase']);
Route::post('test-image-upload', [\App\Http\Controllers\TestController::class, 'uploadImage']);
Route::post('test-image-delete', [\App\Http\Controllers\TestController::class, 'deleteImage'])  ;
Route::get('verify-user/{email}', [\App\Http\Controllers\TestController::class, 'verifyUser']);
Route::get('delete-user/{email}', [\App\Http\Controllers\TestController::class, 'deleteUser']);
Route::get('active', function () {
    return response()->json([
        'success' => true,
        'token' => '14|Aw8HbqJUIaFVFuYU0A2Xz3OqLRvWo5yu0Az64CJj9cd0130d',
        'data' => new \App\Http\Resources\UserResource(\App\Models\User::latest()->first())
    ]);
});
Route::get('list-envs', [\App\Http\Controllers\TestController::class, 'listEnvs'])->middleware('auth:sanctum');

Route::post('test-payment', [\App\Http\Controllers\TestController::class, 'testPayment']);
Route::get('approve-photo/{photo}', [\App\Http\Controllers\TestController::class, 'approvePhoto']);

Route::get('image-test', [\App\Http\Controllers\TestController::class, 'imageTest']);

Route::get('pass', function () {
   return fake()->password(16);
});
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route not found'
    ], 404);
});

