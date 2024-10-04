<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HiringResource extends JsonResource
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
            'creative_id' => $this->creative_id,
            'regular_user_id' => $this->regular_user_id,
            'hire_date' => $this->hire_date,
            'location' => $this->location,
            'num_days' => $this->num_days,
            'num_hours' => $this->num_hours,
            'description' => $this->description,
        ];
    }
}
