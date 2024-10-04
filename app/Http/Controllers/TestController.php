<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryStorage;
use App\Services\ImageStorageManager;
use App\Services\LocalStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TestController extends Controller
{
    public function image()
    {

    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        $storageService = config('app.use_cloudinary') ? new CloudinaryStorage() : new LocalStorage();
        $imageStorageManager = new ImageStorageManager($storageService);

        // Upload the image
        $cloudder_result = $imageStorageManager->upload($request->image->getRealPath(), 'Test');

        $imagePath = Str::after($cloudder_result['public_id'], 'Test/');
        return $imagePath;
    }
}
