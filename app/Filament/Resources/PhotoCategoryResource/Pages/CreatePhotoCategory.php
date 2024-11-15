<?php

namespace App\Filament\Resources\PhotoCategoryResource\Pages;

use App\Filament\Resources\PhotoCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreatePhotoCategory extends CreateRecord
{
    protected static string $resource = PhotoCategoryResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;

        // Check if metadata exists in the request
        if (request()->has('image_public_id') && request()->has('image_url')) {
            $record->update([
                'image_public_id' => request('image_public_id'),
                'image_url' => request('image_url'),
            ]);

            Log::info('Image details saved after create:', [
                'image_public_id' => request('image_public_id'),
                'image_url' => request('image_url'),
            ]);
        }
    }
}
