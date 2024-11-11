<?php

namespace App\Http\Resources;

use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'phone_number' => $this->phone_number,
            'ghana_post_gps' => $this->ghana_post_gps,
            'city' => $this->city,
            'physical_address' => $this->physical_address,
            'creative_hire_status' => $this->creative_hire_status,
            'creative_status' => $this->creative_status,
            'profile_picture' => $this->profile_picture_url,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'pricing' => new PricingResource($this->whenLoaded('pricing')),
            'payment_information' => new PaymentInformationResource($this->whenLoaded('paymentInfo')),
            'creative_categories' => CreativeCategoryResource::collection($this->whenLoaded('creative_categories')),
            'hiring_info' => new HiringResource($this->whenLoaded('hiring')),
            'photos' => PhotoResource::collection($this->whenLoaded('photos')),
            'permissions' => [
                'can_upload' => auth()->can('create', Photo::class)
            ]
        ];
    }
}
