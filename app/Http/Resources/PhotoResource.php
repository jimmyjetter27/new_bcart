<?php

namespace App\Http\Resources;

use App\Services\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotoResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $isUploader = $user && $user->id === $this->user_id;
        $hasPurchased = $user ? $this->hasPurchasedPhoto($user->id, $this->id) : false;

        // Check if we should apply the watermark
        $imageUrl = $this->getImageUrl();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'image_url' => $imageUrl,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'creative' => new UserResource($this->whenLoaded('creative')),
            'photo_categories' => PhotoCategoryResource::collection($this->whenLoaded('photo_categories')),
        ];
    }

    /**
     * Determine the appropriate image URL to return.
     *
     * @return string
     */
    private function getImageUrl()
    {
        $user = auth('sanctum')->user();
        $isUploader = $user && $user->id === $this->user_id;
//        dd([
//            'user' => $user->id,
//            'photo user id' => $this->user_id
//        ]);
        $hasPurchased = $user ? $this->hasPurchasedPhoto($user->id) : false;

        $imageHelper = app(ImageHelper::class);

        dd([
            'user' => $user,
            'photo_user_id' => $this->user_id,
            'isUploader' => $isUploader,
            'hasPurchased' => $hasPurchased
        ]);
        if ($isUploader || $hasPurchased) {
            dd([
                'message' => 'is uploader or has purchased',
                'isUploader' => $isUploader,
                'hasPurchased' => $hasPurchased
            ]);
            // Return the signed URL to the original image
            return $imageHelper->getSignedImageUrl($this->image_public_id);
        } else {
            dd('add watermark');
            // Return the watermarked image URL
            return $imageHelper->applyCloudinaryWatermark($this->image_public_id);
        }
    }


    /**
     * Apply watermark to the image and return the URL.
     *
     * @return string
     */
    private function applyWatermark()
    {
        $imageHelper = app(ImageHelper::class);

        if ($this->isStoredInCloudinary()) {
            // Use the correct base image public_id including the folder
            return $imageHelper->applyCloudinaryWatermark($this->image_public_id);
        }

        return $imageHelper->applyLocalWatermark($this->image_url)->encode('data-url');
    }
}
