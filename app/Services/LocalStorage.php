<?php

namespace App\Services;

use App\Contracts\ImageStorageInterface;
use Illuminate\Support\Facades\Storage;

class LocalStorage implements ImageStorageInterface
{
    public function upload($imagePath, $folder): array
    {
        $path = Storage::putFile($folder, $imagePath);

        return [
            'public_id' => $path,
            'secure_url' => Storage::path($path)
        ];
    }
}
