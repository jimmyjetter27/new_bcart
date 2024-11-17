<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        $record = $this->record;

        if (request()->hasFile('avatar')) {
            $imageFile = request()->file('avatar');

            // Use the ImageStorageInterface to handle the upload
            $imageStorage = app(\App\Contracts\ImageStorageInterface::class);

            // Delete the old image if it exists
            if ($record->profile_picture_public_id) {
                $imageStorage->delete('avatars/' . $record->profile_picture_public_id);
            }

            // Generate the new public ID for Cloudinary
            $publicId = $record->profile_picture_public_id;
            $result = $imageStorage->upload($imageFile, 'avatars', $publicId);

            // Update the record with the new image details
            $record->update([
                'profile_picture_public_id' => $result['public_id'],
                'profile_picture_url' => $result['secure_url'], // Save the latest secure URL
            ]);

        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (request()->has('profile_picture_public_id') && request()->has('profile_picture_url')) {
            $data['profile_picture_public_id'] = request('profile_picture_public_id');
            $data['profile_picture_url'] = request('profile_picture_url');
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
