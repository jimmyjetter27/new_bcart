<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CreativeCategoryController;
use App\Http\Controllers\CreativeController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\HiringController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PhotoCategoryController;
use App\Http\Controllers\PhotoController;
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

        Route::put('approve-photo', [PhotoController::class, 'approvePhoto']);

        Route::post('buy-photos', [OrderController::class, 'buyPhotos']);
        Route::get('user-purchased-photos', [PhotoController::class, 'listPurchasedPhotos']);
        Route::get('download-photo/{photo}', [PhotoController::class, 'downloadPhoto']);

        Route::post('hire-creative', [HiringController::class, 'store']);
    });

    Route::get('search-user', [\App\Http\Controllers\UserController::class, 'search']);
    Route::get('search-creative', [CreativeController::class, 'search']);
    Route::get('search-photo', [PhotoController::class, 'search']);
    Route::get('related-images/{photo}', [PhotoController::class, 'relatedImages']);

    Route::get('featured-creative', [CreativeController::class, 'featuredCreative']);
    Route::get('featured-creatives', [CreativeController::class, 'featuredCreatives']);
    Route::get('featured-creative-categories', [CreativeCategoryController::class, 'featuredCreativeCategories']);

    Route::get('user-{user}-photos', [PhotoController::class, 'getUserPhotos']);


    Route::apiResources([
        'creatives' => CreativeController::class,
        'creative-categories' => CreativeCategoryController::class,
        'photo-categories' => PhotoCategoryController::class,
        'photos' => PhotoController::class
    ]);

    Route::post('suggest-upload', [\App\Http\Controllers\SuggestionController::class, 'store']);

    Route::get('paystack-supported-banks', [\App\Http\Controllers\PaystackController::class, 'listBanks']);
    Route::get('paystack-callback', [\App\Http\Controllers\TransactionController::class, 'paystackCallback']);

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
   return env('FRONTEND_URL');
});
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route not found'
    ], 404);
});

