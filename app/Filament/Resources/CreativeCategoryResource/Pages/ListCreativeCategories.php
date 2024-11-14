<?php

namespace App\Filament\Resources\CreativeCategoryResource\Pages;

use App\Filament\Resources\CreativeCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCreativeCategories extends ListRecords
{
    protected static string $resource = CreativeCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
