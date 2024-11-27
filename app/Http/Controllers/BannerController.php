<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoResource;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BannerController extends Controller
{
    public function show()
    {
        // First, check if a manual banner is set
        $manualBanner = Photo::where('is_banner', true)->first();

        if ($manualBanner) {
            // Cache the manual banner for the day
            Cache::put('banner_photo', $manualBanner, now()->addDay());

            $manualBanner->load('creative');
            return response()->json([
                'success' => true,
                'message' => 'Banner image retrieved successfully.',
                'data' => new PhotoResource($manualBanner),
            ]);
        }

        // If no manual banner, retrieve from cache or select automatically
        $bannerPhoto = Cache::remember('banner_photo', now()->addDay(), function () {
            return Photo::where('is_approved', true)->inRandomOrder()->first();
        });

        if (!$bannerPhoto) {
            return response()->json([
                'success' => false,
                'message' => 'No banner image available.',
            ], 404);
        }

        $bannerPhoto->load('creative');
        return response()->json([
            'success' => true,
            'message' => 'Banner image retrieved successfully.',
            'data' => new PhotoResource($bannerPhoto),
        ]);
    }
}
