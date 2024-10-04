<?php

namespace App\Services;

use App\Contracts\ImageStorageInterface;
use JD\Cloudder\Facades\Cloudder;


class CloudinaryStorage implements ImageStorageInterface
{
    protected $cloudinary;

    public function __construct()
    {
        // Initialize Cloudinary
        $this->cloudinary = new Cloudinary();
    }
    public function upload($imagePath, $folder): array
    {
        $response = Cloudder::upload($imagePath, null, [
            'folder' => $folder
        ]);

        return $response->getResult();
    }
}
