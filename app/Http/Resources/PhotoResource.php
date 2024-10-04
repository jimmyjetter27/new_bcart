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
            'name' => $this->name,
            'slug' => $this->slug,
            'image_path' => $this->image_path,
            'description' => $this->description,
            'price' => $this->price,
            'is_approved' => $this->is_approved,
            'photo_category_id' => $this->photo_category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'creative' => new UserResource($this->whenLoaded('creative')),
            'photo_categories' => PhotoCategoryResource::collection($this->whenLoaded('photo_categories')),
        ];
    }
}
