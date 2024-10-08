<?php

namespace App\Services;

use App\Contracts\ImageStorageInterface;
use Cloudinary\Cloudinary;
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
    public function upload($imageFile, $folder): array
    {
        // Generate a unique temporary file name
        $tempFilePath = tempnam(sys_get_temp_dir(), 'upload_');
        $extension = $imageFile->getClientOriginalExtension();
        $tempFileWithExtension = $tempFilePath . '.' . $extension;

        // Move the uploaded file to the temporary file with the correct extension
        $imageFile->move(dirname($tempFilePath), basename($tempFileWithExtension));

        // Replace backslashes with forward slashes (Windows compatibility)
        $tempFileWithExtension = str_replace('\\', '/', $tempFileWithExtension);

        // Upload the file to Cloudinary
        $response = $this->cloudinary->uploadApi()->upload($tempFileWithExtension, [
            'folder' => $folder,
        ]);

        // Delete the temporary file
        unlink($tempFileWithExtension);

//        return $response->getArrayCopy();
        return [
            'public_id' => Str::after($response['public_id'], $folder.'/'),
            'secure_url' => $response['secure_url'],
        ];
    }

}
