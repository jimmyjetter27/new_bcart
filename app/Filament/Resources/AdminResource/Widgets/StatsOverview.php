<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\Creative;
use App\Models\Order;
use App\Models\Photo;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Users', User::count())
                ->descriptionIcon('heroicon-m-users'),

            Stat::make('Creatives', Creative::count())
                ->descriptionIcon('heroicon-m-users'),

            Stat::make('Photos', Photo::count())
                ->descriptionIcon('heroicon-m-users'),
//                ->url('')
//                ->url(route('filament.resources.users.index')), // Link to users page

            Stat::make('Total Orders', Order::count())
                ->descriptionIcon('heroicon-m-arrow-trending-up')
//                ->url(route('filament.resources.orders.index')), // Link to orders page
        ];
    }
}
