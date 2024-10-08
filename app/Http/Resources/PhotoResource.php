<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'image_url' => $this->image_url,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'creative' => new UserResource($this->whenLoaded('creative')),
//            'photo_category' => new PhotoCategoryResource($this->whenLoaded('photo_category'))
            'photo_categories' => PhotoCategoryResource::collection($this->whenLoaded('photo_categories')),
        ];
    }
}
