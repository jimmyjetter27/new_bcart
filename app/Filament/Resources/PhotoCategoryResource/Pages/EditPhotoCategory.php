<?php

namespace App\Filament\Resources\PhotoCategoryResource\Pages;

use App\Filament\Resources\PhotoCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EditPhotoCategory extends EditRecord
{
    protected static string $resource = PhotoCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        if (request()->hasFile('image')) {
            $imageFile = request()->file('image');

            // Use the ImageStorageInterface to handle the upload
            $imageStorage = app(\App\Contracts\ImageStorageInterface::class);

            // Delete the old image if it exists
            if ($record->image_public_id) {
                $imageStorage->delete('photo_categories/' . $record->image_public_id);
            }

            // Upload the new image using the same public ID or category name
            $publicId = Str::slug($record->photo_category);
            $result = $imageStorage->upload($imageFile, 'photo_categories', $publicId);

            Log::info('Cloudinary upload result (after edit):', $result);

            // Update the record with the new image details
            $record->update([
                'image_public_id' => $result['public_id'],
                'image_url' => $result['secure_url'], // Save the latest secure_url
            ]);

            Log::info('Image details updated after edit:', [
                'image_public_id' => $result['public_id'],
                'image_url' => $result['secure_url'],
            ]);
        }
    }

}
