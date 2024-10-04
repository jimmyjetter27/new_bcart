<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentInformationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'bank_name' => $this->bank_name,
            'bank_branch' => $this->bank_branch,
            'bank_acc_name' => $this->bank_acc_name,
            'bank_acc_num' => $this->bank_acc_num,
            'momo_acc_name' => $this->momo_acc_name,
            'preferred_payment_account' => $this->preferred_payment_account,
        ];
    }
}
