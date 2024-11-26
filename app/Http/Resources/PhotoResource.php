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

        // Define threshold for large images
        $largeImageThreshold = 1000; // Adjust as needed

        // Determine if the image is large
        $isLargeImage = $this->image_width >= $largeImageThreshold && $this->image_height >= $largeImageThreshold;

        // Set row_span and col_span
        if ($isLargeImage) {
            $rowSpan = 2;
            $colSpan = 2;
        } else {
            $rowSpan = 1;
            $colSpan = 1;
        }

        $hasPurchased = $this->checkIfPurchased();

        $isUploader = $user && $user->id === $this->user_id;


        // Check if we should apply the watermark
        $imageUrl = $this->getImageUrl();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'image_url' => $imageUrl,
            'row_span' => $rowSpan,
            'col_span' => $colSpan,
            'is_approved' => (int)$this->is_approved,
            'has_purchased' => (int)$hasPurchased,
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
        $freeImage = $this->freeImage();
        $imageHelper = app(ImageHelper::class);

        if ($freeImage) {
            return $this->image_url;
        }

        $hasPurchased = $this->checkIfPurchased();

        if ($hasPurchased) {
            return $imageHelper->getSignedImageUrl($this->image_public_id); // Signed URL for owner/purchased
        } else {
            return $this->applyWatermark();
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
//            dd($this->image_width);
            // Use the correct base image public_id including the folder
            return $imageHelper->applyCloudinaryWatermark($this->image_public_id, $this->image_width, $this->image_height);
        }

        return $imageHelper->applyLocalWatermark($this->image_url)->encode('data-url');
    }

    /**
     * Determine if the current user has purchased the photo.
     *
     * @return bool
     */
    private function checkIfPurchased()
    {
        $user = auth('sanctum')->user();
        $guestIdentifier = request()->header('X-Guest-Identifier');

        // Log the IDs for debugging
        $userId = $user ? $user->id : 'null';
        Log::info("User ID: {$userId}, Photo User ID: {$this->user_id}");

        $isUploader = $user && intval($user->id) === intval($this->user_id);

        if ($isUploader) {
            return true;
        } elseif ($user) {
            return $this->hasPurchasedPhoto($user->id);
        } elseif ($guestIdentifier) {
            return $this->hasPurchasedPhoto(null, $guestIdentifier);
        }

        return false;
    }

}
