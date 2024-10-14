<?php

namespace App\Services;

use App\Contracts\ImageStorageInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;


class LocalStorage implements ImageStorageInterface
{
    public function upload($imagePath, $folder, $publicId = null, $authenticated = false): array
    {
        // Check if a public_id is provided for replacement
        if ($publicId) {
            // Construct the full path using the old public_id to overwrite the image
            $path = $folder . '/' . $publicId;

            // Overwrite the existing image with the new one
            Storage::put($path, file_get_contents($imagePath));
        } else {
            // If no public_id is passed, upload as a new file
            $path = Storage::putFile($folder, $imagePath);
        }

        return [
            'public_id' => Str::after($path, $folder.'/'),
            'secure_url' => Storage::url($path)
        ];
    }





    public function delete($publicId, $authenticated = false): bool
    {
        // Construct the full file path based on the public ID
        $filePath = $publicId;

        // Delete the file from storage
        return Storage::delete($filePath);
    }
}
