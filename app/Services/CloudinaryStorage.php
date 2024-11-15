<?php

namespace App\Services;

use App\Contracts\ImageStorageInterface;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class CloudinaryStorage implements ImageStorageInterface
{
    protected $cloudinary;

    public function __construct()
    {


        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key'    => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
        ]);
    }

    public function upload($imageFile, $folder, $publicId = null, $authenticated = false): array
    {
//        dd([
//            [
//                'public_id' => $publicId,
//                'original_name' => $imageFile->getClientOriginalName(),
//                'folder' => $folder,
//                'authenticated' => $authenticated,
//            ]
//        ]);
        Log::info('Uploading file...', [
            'public_id' => $publicId,
            'original_name' => $imageFile->getClientOriginalName(),
            'folder' => $folder,
            'authenticated' => $authenticated,
        ]);

        // Generate a unique filename for the temporary storage
        $extension = $imageFile->getClientOriginalExtension();
        $tempFileName = Str::random(10) . '.' . $extension;

        // Save the file to Laravel's 'temp' disk
        $tempFilePath = Storage::disk('temp')->putFileAs('', $imageFile, $tempFileName);
        $tempFileFullPath = Storage::disk('temp')->path($tempFileName);

        Log::info('File temporarily stored', ['temp_path' => $tempFileFullPath]);

        try {
            // Upload the file to Cloudinary
            $uploadOptions = [
                'folder' => $folder,
                'public_id' => $publicId ?? null,
            ];

            if ($authenticated) {
                $uploadOptions['type'] = 'authenticated';
            }

            $response = $this->cloudinary->uploadApi()->upload($tempFileFullPath, $uploadOptions);

//            Log::info('Cloudinary upload response', ['response' => $response]);

            // Delete the temporary file from the 'temp' disk
            Storage::disk('temp')->delete($tempFileName);
//            Log::info('Temporary file deleted');

            return [
                'public_id' => Str::after($response['public_id'], $folder . '/'),
                'secure_url' => $response['secure_url'],
            ];
        } catch (\Exception $e) {
            // Cleanup and rethrow the exception
            Storage::disk('temp')->delete($tempFileName);
            Log::error('Cloudinary upload failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete($publicId, $authenticated = false): bool
    {
        // Set deletion options based on authentication status
        $deleteOptions = [];
        if ($authenticated) {
            $deleteOptions['type'] = 'authenticated';
        }

        // Perform the deletion
        $response = $this->cloudinary->uploadApi()->destroy($publicId, $deleteOptions);

        // Check if deletion was successful
        return $response['result'] === 'ok';
    }

    public function deleteMultiple(array $publicIds, $authenticated = false): array
    {
        $results = [];
        foreach ($publicIds as $publicId) {
            try {
                $result = $this->delete($publicId, $authenticated);
                $results[$publicId] = $result ? 'deleted' : 'failed';
            } catch (\Exception $e) {
                Log::error('Error deleting image: ' . $publicId, ['error' => $e->getMessage()]);
                $results[$publicId] = 'error';
            }
        }
        return $results;
    }


}
