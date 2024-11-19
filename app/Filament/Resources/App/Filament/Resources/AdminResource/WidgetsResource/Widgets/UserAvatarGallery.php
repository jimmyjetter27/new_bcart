<?php

namespace App\Filament\Resources\App\Filament\Resources\AdminResource\WidgetsResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserAvatarGallery extends BaseWidget
{
    protected static string $view = 'filament.widgets.user-avatar-gallery';
    protected function getStats(): array
    {
        return [
            'users' => User::select(['id', 'profile_picture_url', 'first_name', 'last_name'])
                ->whereNotNull('profile_picture_url')
                ->take(10) // Limit to recent 10 users with avatars
                ->get(),
        ];
    }
}
