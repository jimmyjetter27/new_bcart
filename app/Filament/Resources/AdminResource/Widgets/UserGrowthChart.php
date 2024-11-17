<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class UserGrowthChart extends Widget
{
    protected static string $view = 'filament.widgets.user-growth-chart';

    public function getData(): array
    {
        $usersPerMonth = \App\Models\User::query();

        if (DB::getDriverName() === 'sqlite') {
            $usersPerMonth->selectRaw('strftime("%m", created_at) as month, COUNT(*) as count');
        } else {
            $usersPerMonth->selectRaw('MONTH(created_at) as month, COUNT(*) as count');
        }

        $usersPerMonth = $usersPerMonth->groupBy('month')->pluck('count', 'month');

        return [
            'labels' => $usersPerMonth->keys(),
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $usersPerMonth->values(),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                ],
            ],
        ];
    }
}
