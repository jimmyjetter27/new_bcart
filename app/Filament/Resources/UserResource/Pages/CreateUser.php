<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;

        // Debug request data
        Log::info('Request data in afterCreate:', request()->all());

        // Check if image metadata exists in the request
        if (request()->has('profile_picture_public_id') && request()->has('profile_picture_url')) {
            $record->update([
                'profile_picture_public_id' => request('profile_picture_public_id'),
                'profile_picture_url' => request('profile_picture_url'),
            ]);

            Log::info('Updated user record with avatar metadata:', [
                'profile_picture_public_id' => request('profile_picture_public_id'),
                'profile_picture_url' => request('profile_picture_url'),
            ]);
        } else {
            Log::warning('Avatar metadata missing in request.');
        }
    }

}
