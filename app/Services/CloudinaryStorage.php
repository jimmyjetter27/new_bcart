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

        // Delete the temporary file
        unlink($tempFileWithExtension);

//        return $response->getArrayCopy();
        return [
            'public_id' => Str::after($response['public_id'], $folder.'/'),
            'secure_url' => $response['secure_url'],
        ];
    }





    public function delete($publicId): bool
    {
//        Log::info('publicId: '. $publicId);
        // destroy method to delete the image by public ID
        $response = $this->cloudinary->uploadApi()->destroy($publicId);

        // Check if deletion was successful
        return $response['result'] === 'ok';
    }

}
