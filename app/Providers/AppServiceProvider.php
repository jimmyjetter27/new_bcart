<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);


        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            Log::info('verification url: ' . $url);

            // Parse the original URL and extract the query parameters
            $parsedUrl = parse_url($url);
            parse_str($parsedUrl['query'], $queryParams);

            // Ensure that 'id', 'hash', 'expires', and 'signature' exist in the query parameters
            if (!isset($queryParams['expires']) || !isset($queryParams['signature'])) {
                Log::error('Missing expected query parameters in verification URL', [
                    'queryParams' => $queryParams,
                ]);
                throw new \Exception('The verification URL is missing required parameters.');
            }

            // Build the frontend verification link using the extracted parameters
            $verification_link = env('FRONTEND_URL') . '/verification?' . http_build_query([
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                    'expires' => $queryParams['expires'],
                    'signature' => $queryParams['signature'],
                ]);

            return (new MailMessage)
                ->subject('Verify Email Address')
                ->line('Click the button below to verify your email address.')
                ->action('Verify Email Address', $verification_link)
                ->line('If you did not create an account, no further action is required.');
        });
    }
}
