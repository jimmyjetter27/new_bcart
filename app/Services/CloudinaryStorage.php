<?php

namespace App\Services;

use App\Contracts\ImageStorageInterface;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class CloudinaryStorage implements ImageStorageInterface
{
    protected $cloudinary;

    public function __construct()
    {

        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');
        $cloudinaryUrl = env('CLOUDINARY_URL'); // Cloudinary may rely on this

        \Log::info('Cloudinary Environment Check', [
            'cloud_name' => $cloudName,
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'cloudinary_url' => $cloudinaryUrl,
        ]);

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
    }
    public function upload($imageFile, $folder, $publicId = null, $authenticated = false): array
    {
//        Log::info('Cloudinary Config: ', [
//            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
//            'api_key' => env('CLOUDINARY_API_KEY'),
//            'api_secret' => env('CLOUDINARY_API_SECRET'),
//        ]);

//        Log::info(json_encode([
//            'publicId' => $publicId,
//            'folder' => $folder,
//            'authenticated' => $authenticated
//        ]));

        // Generate a unique temporary file name
        $tempFilePath = tempnam(sys_get_temp_dir(), 'upload_');
        $extension = $imageFile->getClientOriginalExtension();
        $tempFileWithExtension = $tempFilePath . '.' . $extension;

        // Move the uploaded file to the temporary file with the correct extension
        $imageFile->move(dirname($tempFilePath), basename($tempFileWithExtension));

        // Replace backslashes with forward slashes (Windows compatibility)
        $tempFileWithExtension = str_replace('\\', '/', $tempFileWithExtension);

        // Set optional parameters

        // Upload the file to Cloudinary

        // Upload the file to Cloudinary
        $uploadOptions = [
            'folder'    => $folder,
            'public_id' => $publicId ?? null,
        ];

        if ($authenticated) {
            $uploadOptions['type'] = 'authenticated';
        }

        $response = $this->cloudinary->uploadApi()->upload($tempFileWithExtension, $uploadOptions);
//        Log::info('Cloudinary upload response:', $response->getArrayCopy());

        // Delete the temporary file
        unlink($tempFileWithExtension);

//        return $response->getArrayCopy();
        return [
            'public_id' => Str::after($response['public_id'], $folder.'/'),
            'secure_url' => $response['secure_url'],
        ];
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
