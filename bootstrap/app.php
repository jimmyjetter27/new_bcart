<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (ValidationException $validationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validationException->validator->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ]);
        });
        // Catch all other exceptions here
        $exceptions->render(function (Exception $exception) {
            \Illuminate\Support\Facades\Log::debug('Exception: '. $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
//                'error' => $exception->getMessage()
            ], 500);
        });

        $exceptions->render(function (Throwable $throwable) {
            \Illuminate\Support\Facades\Log::debug('Exception: '. $throwable->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
//                'error' => $throwable->getMessage()
            ], 500);
        });

        //
    })->create();
