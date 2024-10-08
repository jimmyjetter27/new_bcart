<?php

namespace App\Services;

use App\Contracts\ImageStorageInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalStorage implements ImageStorageInterface
{
    public function upload($imagePath, $folder): array
    {
        $path = Storage::putFile($folder, $imagePath);

        return [
            'public_id' => Str::after($path, $folder.'/'),
            'secure_url' => Storage::url($path)
        ];
    }
}
