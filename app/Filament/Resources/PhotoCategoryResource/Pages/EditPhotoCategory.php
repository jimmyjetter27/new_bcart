<?php

namespace App\Filament\Resources\PhotoCategoryResource\Pages;

use App\Filament\Resources\PhotoCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhotoCategory extends EditRecord
{
    protected static string $resource = PhotoCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
