<?php

namespace App\Providers;

use App\Contracts\ImageStorageInterface;
use App\Services\CloudinaryStorage;
use App\Services\LocalStorage;
use Illuminate\Support\ServiceProvider;

class ImageStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ImageStorageInterface::class, function ($app) {
            $storageService = config('image.storage');

            return match ($storageService) {
                'cloudinary' => new CloudinaryStorage(),
                default => new LocalStorage(),
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
