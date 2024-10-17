<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CreativeHiringSettingRequest extends FormRequest
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
            'phone_number' => 'required|regex:/^0[0-9]{9}$/',
            'ghana_post_gps' => 'required',
            'city' => 'required',
            'physical_address' => 'required',
            'description' => 'nullable|string',
            'creative_hire_status' => 'required|boolean',

            // creative pricing payload
            'pricing.hourly_rate' => 'nullable|numeric',
            'pricing.daily_rate' => 'nullable|numeric',
            'pricing.minimum_charge' => 'nullable|numeric',
            'pricing.one_day_traditional' => 'nullable|numeric',
            'pricing.one_day_white' => 'nullable|numeric',
            'pricing.one_day_white_traditional' => 'nullable|numeric',
            'pricing.two_days_white_traditional' => 'nullable|numeric',
            'pricing.three_days_thanksgiving' => 'nullable|numeric',
            'pricing.other_charges' => 'nullable|string',

            // payment information
            'payment_details.bank_name' => 'nullable|string',
            'payment_details.bank_branch' => 'nullable|string',
            'payment_details.bank_acc_name' => 'nullable|string',
            'payment_details.bank_acc_num' => [
                'nullable',
                'string',
                Rule::unique('payment_information', 'bank_acc_num')->ignore($this->user()->paymentInfo?->id)
            ],
            'payment_details.momo_acc_name' => 'nullable|string',
            'payment_details.momo_acc_number' => [
                'nullable',
                'numeric',
                Rule::unique('payment_information', 'momo_acc_number')->ignore($this->user()->paymentInfo?->id)
            ],
            'payment_details.preferred_payment_account' => 'nullable|in:bank_account,momo',

            'creative_categories' => 'required|array',
            'creative_categories.*' => 'exists:creative_categories,id',
        ];
    }

    public function messages()
    {
        return [
            'payment_details.bank_acc_num.unique' => 'The bank account number you provided is already in use. Please provide a different account number.',
            'payment_details.momo_acc_number.unique' => 'The mobile money number you provided is already in use. Please provide a different mobile money number.',
        ];
    }
}
