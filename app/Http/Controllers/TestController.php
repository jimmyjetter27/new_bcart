<?php

namespace App\Http\Controllers;

use App\Contracts\ImageStorageInterface;
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

    public function uploadImage(Request $request, ImageStorageInterface $imageStorage)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $uploadedFile = $request->file('image');

        // Upload the image
        $result = $imageStorage->upload($uploadedFile, 'Test/Me');

        return $result;
    }


}
