<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryStorage;
use App\Services\ImageStorageManager;
use App\Services\LocalStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
//        $storageService = config('app.use_cloudinary') ? new CloudinaryStorage() : new LocalStorage();
//        $imageStorageManager = new ImageStorageManager($storageService);
//
//        // Upload the image
//        $cloudder_result = $imageStorageManager->upload($request->image->getRealPath(), 'Test');
//
//        $imagePath = Str::after($cloudder_result['public_id'], 'Test/');
//        return $imagePath;

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // Store image using the configured disk (Cloudinary in this case)
            $path = Storage::disk(env('FILESYSTEM_DRIVER', 'cloudinary'))->put('images', $file);

            // Get the URL for the uploaded file
            $url = Storage::disk(env('FILESYSTEM_DRIVER', 'cloudinary'))->url($path);

            return response()->json(['url' => $url], 200);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
