<?php

namespace App\Services;

use App\Contracts\ImageStorageInterface;

class ImageStorageManager
{
    protected $storageService;

    public function __construct(ImageStorageInterface $storageService)
    {
        $this->storageService = $storageService;
    }

    public function upload($imagePath, $folder)
    {
        return $this->storageService->upload($imagePath, $folder);

    }
}
