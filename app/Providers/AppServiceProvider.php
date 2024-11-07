<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
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
            // Extract the user id and hash from the generated verification URL
            $parsedUrl = parse_url($url);
            parse_str($parsedUrl['query'], $queryParams);

            // Generate a frontend URL with the necessary parameters
            $verificationLink = env('FRONTEND_URL') . '/verification?' . http_build_query([
                    'id' => $queryParams['id'],
                    'hash' => $queryParams['hash']
                ]);

            return (new MailMessage)
                ->subject('Verify Email Address')
                ->line('Click the button below to verify your email address.')
                ->action('Verify Email Address', $verificationLink)
                ->line('If you did not create an account, no further action is required.');
        });
    }
}
