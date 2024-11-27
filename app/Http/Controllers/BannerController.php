<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoResource;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function show()
    {
        // Attempt to get the banner photo from cache
        $bannerPhoto = cache()->remember('banner_photo', now()->addDay(), function () {
            // If not in cache, get from the database
            return \App\Models\Photo::where('is_banner', true)->first();
        });

        if (!$bannerPhoto) {
            return response()->json([
                'success' => false,
                'message' => 'No banner image set.',
            ], 404);
        }

        // Return the photo resource
        return response()->json([
            'success' => true,
            'message' => 'Banner image retrieved successfully.',
            'data' => new PhotoResource($bannerPhoto),
        ]);
    }
}
