<?php

namespace App\Contracts;

interface ImageStorageInterface
{
    public function upload($imagePath, $folder, $publicId = null, $authenticated = false): array; // Return an array with 'public_id' and 'secure_url'
    public function delete($publicId): bool;
}
