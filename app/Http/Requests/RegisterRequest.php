<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
//            'first_name' => 'required|string|min:3|max:20',
//            'last_name' => 'required|string|min:3|max:20',
//            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:3',
//            'location' => 'required|string',
//            'region' => 'nullable|string',
//            'description' => 'nullable|string',
//            'type' => 'nullable|string|in:regular_user,creative',
//            'profile_image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',

            // creative pricing payload
            'pricing.hourly_rate' => 'nullable|string',
            'pricing.daily_rate' => 'nullable|string',
            'pricing.minimum_charge' => 'nullable|string',

            // wedding pricing
            'wedding_pricing.one_day_traditional' => 'nullable|numeric',
            'wedding_pricing.one_day_white' => 'nullable|numeric',
            'wedding_pricing.one_day_white&traditional' => 'nullable|numeric',
            'wedding_pricing.two_days_white&traditional' => 'nullable|numeric',
            'wedding_pricing.three_days_thanksgiving' => 'nullable|numeric',
            'wedding_pricing.other_charges' => 'nullable|string',

            // payment information
            'payment_details.bank_name' => 'nullable|string',
            'payment_details.bank_branch' => 'nullable|string',
            'payment_details.bank_acc_name' => 'nullable|string',
            'payment_details.bank_acc_num' => 'nullable',
            'payment_details.momo_acc_name' => 'nullable|string',
            'payment_details.momo_acc_number' => 'nullable|numeric',

            // hiring details..frontend guy passes category ids
            'hiring_details.category_ids' => 'array',
            'hiring_details.category_ids.*' => 'exists:creative_categories,id'
        ];
    }
}
