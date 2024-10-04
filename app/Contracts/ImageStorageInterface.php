<?php

namespace App\Contracts;

interface ImageStorageInterface
{
    public function upload($imagePath, $folder): array; // Return an array with 'public_id' and 'secure_url'
}
