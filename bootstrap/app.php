<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
//        $middleware->append(\App\Http\Middleware\VerifyCsrfToken::class);
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
//            \App\Http\Middleware\CustomCors::class
        ]);

        $middleware->validateCsrfTokens(except: [
            'http://localhost:3000',
            'http://localhost:8000',
            'api/*',
            'sanctum/csrf-cookie',

        ]);
//
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

        $exceptions->render(function (MethodNotAllowedHttpException $e) {
           return response()->json([
               'success' => false,
               'message' => $e->getMessage()
           ]);
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.'
            ]);
        });

        // Catch all other exceptions here
        $exceptions->render(function (Exception $exception) {
            \Illuminate\Support\Facades\Log::debug('Exception: '. $exception);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
//                'error' => $exception->getMessage()
            ], 500);
        });

        $exceptions->render(function (Throwable $throwable) {
            \Illuminate\Support\Facades\Log::debug('Exception: '. $throwable);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
//                'error' => $throwable->getMessage()
            ], 500);
        });

        //
    })->create();
