<?php

namespace App\Filament\Resources\HiringResource\Pages;

use App\Filament\Resources\HiringResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHirings extends ListRecords
{
    protected static string $resource = HiringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
