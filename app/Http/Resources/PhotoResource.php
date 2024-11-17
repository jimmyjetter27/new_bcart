<?php

namespace App\Http\Resources;

use App\Services\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

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
            'creative' => $this->whenLoaded('creative', fn() => new UserResource($this->creative), [
                'id' => null,
                'first_name' => 'Deleted',
                'last_name' => 'User',
                'username' => 'deleted_user',
                'profile_picture' => asset('images/default-avatar.png'),
            ]),
            'photo_categories' => PhotoCategoryResource::collection($this->whenLoaded('photo_categories')),
            'tags' => PhotoTagResource::collection($this->whenLoaded('tags')),
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
        $isUploader = $user && intval($user->id) === intval($this->user_id);

        $hasPurchased = $user ? $this->hasPurchasedPhoto($user->id) : false;

        $freeImage = $this->freeImage();

        $imageHelper = app(ImageHelper::class);

//        Log::info(json_encode([
//            'has_purchased' => $hasPurchased,
//            'freeImage' => $freeImage
//        ]));

        if ($freeImage) {
            return $this->image_url;
        } elseif ($isUploader || $hasPurchased) {
            return $imageHelper->getSignedImageUrl($this->image_public_id); // Signed URL for owner/purchased
        } else {
            return $imageHelper->applyCloudinaryWatermark($this->image_public_id); // Watermarked for others
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
