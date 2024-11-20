<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;

class ImageHelper
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
    }

//    public function applyCloudinaryWatermark(string $publicId, string $watermarkPublicId = null)
//    {
//        // Define the default watermark if none is provided
//        $watermarkPublicId = $watermarkPublicId ?? 'Bcart/hppz3vr28ml0pzkr5uov';
//
//        // Replace '/' with ':' in the watermark public_id for the overlay
//        $overlayPublicId = str_replace('/', ':', $watermarkPublicId);
//
//        // Ensure base image public_id includes the folder
//        $baseImagePublicId = 'creative_uploads/' . $publicId;
//
//        $imageUrl = $this->cloudinary->image($baseImagePublicId)
//            ->deliveryType('authenticated')
//            ->version($this->getImageVersion($baseImagePublicId))
//            ->signUrl()
////            ->expiresAt(time() + 3600)
//            ->addTransformation([
//                'overlay' => $overlayPublicId,
//                'gravity' => 'center',
//                'x'       => 10,
//                'y'       => 10,
//                'opacity' => 50,
//                'width'   => 0.3,
//                'flags'   => 'relative',
//                'crop'    => 'scale',
//            ])
//            ->toUrl();
//
//        return $imageUrl;
//    }

    public function applyCloudinaryWatermark(string $publicId, string $watermarkPublicId = null)
    {
        // Define the default watermark if none is provided
        $watermarkPublicId = $watermarkPublicId ?? 'Bcart/hppz3vr28ml0pzkr5uov';

        // Replace '/' with ':' in the watermark public_id for the overlay
        $overlayPublicId = str_replace('/', ':', $watermarkPublicId);

        // Ensure base image public_id includes the folder
        $baseImagePublicId = 'creative_uploads/' . $publicId;

        // Start with the base image
        $image = $this->cloudinary->image($baseImagePublicId)
            ->deliveryType('authenticated')
            ->version($this->getImageVersion($baseImagePublicId))
            ->signUrl();

        // Define overlay positions with smaller offsets to fit within image boundaries
        $positions = [
            ['x' => -350, 'y' => -350], ['x' => 0, 'y' => -350], ['x' => 350, 'y' => -350],
//            ['x' => -300, 'y' => 0],    ['x' => 0, 'y' => 0],    ['x' => 300, 'y' => 0],
            ['x' => -350, 'y' => 350],  ['x' => 0, 'y' => 350],  ['x' => 350, 'y' => 350]
        ];

        // Apply each overlay with diagonal rotation, adjusted size, and offset
        foreach ($positions as $position) {
            $image->addTransformation([
                'overlay' => $overlayPublicId,
                'gravity' => 'center',
                'x'       => $position['x'],
                'y'       => $position['y'],
                'opacity' => 30,       // Adjust for subtlety
                'angle'   => 45,       // Diagonal rotation
                'width'   => 0.10,     // Smaller size for logos
                'flags'   => 'relative',
                'crop'    => 'scale',
            ]);
        }

        // Generate and return the URL
        $imageUrl = $image->toUrl();

        return $imageUrl;
    }



//    public function applyCloudinaryWatermark(string $publicId, string $watermarkPublicId = null)
//    {
//        // Define the default watermark if none is provided
//        $watermarkPublicId = $watermarkPublicId ?? 'Bcart/hppz3vr28ml0pzkr5uov';
//
//        // Replace '/' with ':' in the watermark public_id for the overlay
//        $overlayPublicId = str_replace('/', ':', $watermarkPublicId);
//
//        // Ensure base image public_id includes the folder
//        $baseImagePublicId = 'creative_uploads/' . $publicId;
//
//        // Define the overlay transformation
//        $overlayTransformation = [
//            'overlay' => $overlayPublicId,
//            'opacity' => 50,
//            'width'   => 0.3,
//            'flags'   => 'relative',
//            'crop'    => 'scale',
//        ];
//
//        // Define positions for the watermarks
//        $positions = [
//            ['gravity' => 'north_west', 'x' => 10, 'y' => 10],
//            ['gravity' => 'north_east', 'x' => 7, 'y' => 5],
//            ['gravity' => 'south_west', 'x' => 10, 'y' => 10],
//            ['gravity' => 'south_east', 'x' => 2, 'y' => 9],
//        ];
//
//        // Start with the base image
//        $image = $this->cloudinary->image($baseImagePublicId);
//
//        // Apply each overlay with its position
//        foreach ($positions as $position) {
//            $image = $image->addTransformation(array_merge($overlayTransformation, $position));
//        }
//
//        // Generate the URL
//        $imageUrl = $image->toUrl();
//
//        return $imageUrl;
//    }

    public function getSignedImageUrl(string $publicId)
    {
        // Ensure base image public_id includes the folder
        $baseImagePublicId = 'creative_uploads/' . $publicId;

        // Get the version of the image
        $version = $this->getImageVersion($baseImagePublicId);

        // Generate a signed URL with delivery type 'authenticated'
        $imageUrl = $this->cloudinary->image($baseImagePublicId)
            ->deliveryType('authenticated') // Specify the delivery type
            ->version($version)             // Include the version
            ->signUrl(true)
            ->toUrl();

        return $imageUrl;
    }


    private function getImageVersion($publicId)
    {
        $baseImagePublicId = $publicId;

        try {
            // Fetch the resource details to get the version
            $resource = $this->cloudinary->adminApi()->asset($baseImagePublicId, [
                'type'          => 'authenticated', // Ensure the type matches the upload type
                'resource_type' => 'image',         // Specify the resource type
            ]);
            return $resource['version'];
        } catch (\Cloudinary\Api\Exception\NotFound $e) {
            // Handle the exception if the resource is not found
            Log::error("Resource not found: " . $baseImagePublicId);
            throw $e; // Re-throw the exception or handle it as needed
        } catch (\Exception $e) {
            // Handle other exceptions
            Log::error("Error retrieving resource version: " . $e->getMessage());
            throw $e;
        }
    }



}
