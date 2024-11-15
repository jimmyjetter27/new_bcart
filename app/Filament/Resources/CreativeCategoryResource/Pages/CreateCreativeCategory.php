<?php

namespace App\Filament\Resources\CreativeCategoryResource\Pages;

use App\Filament\Resources\CreativeCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateCreativeCategory extends CreateRecord
{
    protected static string $resource = CreativeCategoryResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;

        // Check if metadata exists in the request
        if (request()->has('image_public_id') && request()->has('image_url')) {
            $record->update([
                'image_public_id' => request('image_public_id'),
                'image_url' => request('image_url'),
            ]);

//            Log::info('Image details saved after create:', [
//                'image_public_id' => request('image_public_id'),
//                'image_url' => request('image_url'),
//            ]);
        }
    }
}
