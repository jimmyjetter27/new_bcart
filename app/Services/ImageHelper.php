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
                'api_key' => env('CLOUDINARY_API_KEY'),
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


//    public function applyCloudinaryWatermark(string $publicId, int $width, int $height, string $watermarkPublicId = null)
//    {
//        // Define the default watermark if none is provided
//        $watermarkPublicId = $watermarkPublicId ?? 'Bcart:hppz3vr28ml0pzkr5uov';
//
//        // Replace '/' with ':' in the watermark public_id for the overlay
//        $overlayPublicId = str_replace('/', ':', $watermarkPublicId);
//
//        // Ensure base image public_id includes the folder
//        $baseImagePublicId = 'creative_uploads/' . $publicId;
//
//        // Adjust the watermark size and spacing
//        $gridSize = 4; // Reduces number of watermarks
//        $watermarkSize = max($width, $height) / $gridSize;
//        $cols = ceil($width / $watermarkSize);
//        $rows = ceil($height / $watermarkSize);
//
//        // Start with the base image
//        $image = $this->cloudinary->image($baseImagePublicId)
//            ->deliveryType('authenticated')
//            ->version($this->getImageVersion($baseImagePublicId))
//            ->signUrl();
//
//        // Distribute watermarks with proper spacing
//        for ($row = 0; $row < $rows; $row++) {
//            for ($col = 0; $col < $cols; $col++) {
//                $xOffset = round(($col * $watermarkSize) - ($width / 2) + ($watermarkSize / 2));
//                $yOffset = round(($row * $watermarkSize) - ($height / 2) + ($watermarkSize / 2));
//
//                $image->addTransformation([
//                    'overlay' => $overlayPublicId,
//                    'gravity' => 'center',
//                    'x'       => $xOffset,
//                    'y'       => $yOffset,
//                    'opacity' => 30,    // Adjust for visibility
//                    'angle'   => 45,    // Rotate the watermark
//                    'width'   => 0.2,   // Adjust size of watermark relative to image
//                    'flags'   => 'relative',
//                    'crop'    => 'scale',
//                ]);
//            }
//        }
//
//        return $image->toUrl();
//    }
    public function applyCloudinaryWatermark(string $publicId, int $width, int $height, string $watermarkPublicId = null)
    {
        // Define the default watermark if none is provided
//        $watermarkPublicId = $watermarkPublicId ?? 'Bcart:hppz3vr28ml0pzkr5uov';
        $watermarkPublicId = $watermarkPublicId ?? 'Bcart:bcart_logo_ko7mnl';

        // Replace '/' with ':' in the watermark public_id for the overlay
        $overlayPublicId = str_replace('/', ':', $watermarkPublicId);

        // Ensure base image public_id includes the folder
        $baseImagePublicId = 'creative_uploads/' . $publicId;

        // Adjust the watermark size and spacing
        $gridSize = 3; // Controls the number of watermarks
        $watermarkSize = max($width, $height) / $gridSize;

        $cols = floor($width / $watermarkSize); // Number of columns
        $rows = floor($height / $watermarkSize); // Number of rows

        // Start with the base image
        $image = $this->cloudinary->image($baseImagePublicId)
            ->deliveryType('authenticated')
            ->version($this->getImageVersion($baseImagePublicId))
            ->signUrl();

        // Distribute watermarks with proper spacing
        for ($row = 0; $row <= $rows; $row++) {
            for ($col = 0; $col <= $cols; $col++) {
                $xOffset = round(($col * $watermarkSize) - ($width / 2) + ($watermarkSize / 2));
                $yOffset = round(($row * $watermarkSize) - ($height / 2) + ($watermarkSize / 2));

                // Ensure the watermark is within the bounds of the image
                if ($xOffset < -($width / 2) || $xOffset > ($width / 2) || $yOffset < -($height / 2) || $yOffset > ($height / 2)) {
                    continue; // Skip if out of bounds
                }

                $image->addTransformation([
                    'overlay' => $overlayPublicId,
                    'gravity' => 'center',
                    'x'       => $xOffset,
                    'y'       => $yOffset,
                    'opacity' => 30,    // Adjust for visibility
                    'angle'   => 45,    // Rotate the watermark
                    'width'   => 0.15,  // Reduce size of watermark relative to image
                    'flags'   => 'relative',
                    'crop'    => 'scale',
                ]);
            }
        }

        return $image->toUrl();
    }



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
                'type' => 'authenticated', // Ensure the type matches the upload type
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
