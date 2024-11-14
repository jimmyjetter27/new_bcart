<?php

namespace App\Filament\Resources\CreativeCategoryResource\Pages;

use App\Filament\Resources\CreativeCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreativeCategory extends EditRecord
{
    protected static string $resource = CreativeCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
