<?php

namespace App\Filament\Resources\CreativeCategoryResource\Pages;

use App\Filament\Resources\CreativeCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditCreativeCategory extends EditRecord
{
    protected static string $resource = CreativeCategoryResource::class;

    protected function afterSave(): void
    {
        $record = $this->record;

        if (request()->hasFile('image')) {
            $imageFile = request()->file('image');

            // Use the ImageStorageInterface to handle the upload
            $imageStorage = app(\App\Contracts\ImageStorageInterface::class);

            // Delete the old image if it exists
            if ($record->image_public_id) {
                $imageStorage->delete('creative_categories/' . $record->image_public_id);
            }

            // Generate the new public ID for Cloudinary
            $publicId = Str::slug($record->creative_category);
            $result = $imageStorage->upload($imageFile, 'creative_categories', $publicId);

            // Update the record with the new image details
            $record->update([
                'image_public_id' => $result['public_id'],
                'image_url' => $result['secure_url'], // Save the latest secure URL
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
