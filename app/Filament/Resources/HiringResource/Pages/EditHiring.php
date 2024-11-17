<?php

namespace App\Filament\Resources\HiringResource\Pages;

use App\Filament\Resources\HiringResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHiring extends EditRecord
{
    protected static string $resource = HiringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
