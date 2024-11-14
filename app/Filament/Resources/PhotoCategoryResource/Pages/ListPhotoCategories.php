<?php

namespace App\Filament\Resources\PhotoCategoryResource\Pages;

use App\Filament\Resources\PhotoCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPhotoCategories extends ListRecords
{
    protected static string $resource = PhotoCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
