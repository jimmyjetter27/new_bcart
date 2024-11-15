<?php

namespace App\Services;

use App\Contracts\ImageStorageInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalStorage implements ImageStorageInterface
{
    /**
     * Uploads an image to local storage.
     *
     * @param string $imagePath Path to the image file.
     * @param string $folder Folder where the image will be stored.
     * @param string|null $publicId Public ID for overwriting existing files.
     * @param bool $authenticated Whether the image is for authenticated access.
     * @param array $options Additional options (not used for local storage).
     * @return array Array containing 'public_id' and 'secure_url'.
     */
    public function upload($imagePath, $folder, $publicId = null, $authenticated = false, $options = []): array
    {
        // Determine the storage path
        if ($publicId) {
            $path = $folder . '/' . $publicId;

            // Overwrite the existing file
            Storage::put($path, file_get_contents($imagePath));
        } else {
            // Generate a unique filename if no public_id is provided
            $extension = pathinfo($imagePath, PATHINFO_EXTENSION);
            $uniqueName = Str::random(10) . '.' . $extension;
            $path = $folder . '/' . $uniqueName;

            // Save the new file
            Storage::put($path, file_get_contents($imagePath));
        }

        // Return public_id and secure_url
        return [
            'public_id' => Str::after($path, $folder . '/'),
            'secure_url' => Storage::url($path),
        ];
    }

    /**
     * Deletes an image from local storage.
     *
     * @param string $publicId Public ID of the file to be deleted.
     * @param bool $authenticated Whether the image is for authenticated access.
     * @return bool True if deletion was successful, false otherwise.
     */
    public function delete($publicId, $authenticated = false): bool
    {
        // Construct the full file path based on the public ID
        $filePath = $publicId;

        // Delete the file from storage
        return Storage::delete($filePath);
    }
}
