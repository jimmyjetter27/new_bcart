<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCreativeCategoryRequest extends FormRequest
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
            'creative_category' => [
                'required',
                Rule::unique('creative_categories')->ignore($this->route('creative_category')->id),
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5000'
        ];
    }
}
