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

            // Generate the new public ID for Cloudinary
            $publicId = Str::slug($record->photo_category);
            $result = $imageStorage->upload($imageFile, 'photo_categories', $publicId);

            Log::info('Cloudinary upload result:', $result);

            // Update the record with the new image details
            $record->update([
                'image_public_id' => $result['public_id'],
                'image_url' => $result['secure_url'], // Save the latest secure URL
            ]);

            Log::info('Updated photo category record:', [
                'id' => $record->id,
                'image_public_id' => $result['public_id'],
                'image_url' => $result['secure_url'],
            ]);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (request()->has('image_public_id') && request()->has('image_url')) {
            $data['image_public_id'] = request('image_public_id');
            $data['image_url'] = request('image_url');
        }

        return $data;
    }
}
