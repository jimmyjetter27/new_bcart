<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'hourly_rate' => $this->hourly_rate,
            'daily_rate' => $this->daily_rate,
            'minimum_charge' => $this->minimum_charge,
            'one_day_traditional' => $this->one_day_traditional,
            'one_day_white' => $this->one_day_traditional,
            'one_day_white_traditional' => $this->one_day_traditional,
            'two_days_white_traditional' => $this->one_day_traditional,
            'three_days_thanksgiving' => $this->one_day_traditional,
            'other_charges' => $this->one_day_traditional,
        ];
    }
}
