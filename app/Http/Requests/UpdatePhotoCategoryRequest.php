<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePhotoCategoryRequest extends FormRequest
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
            'photo_category' => [
                'required',
                Rule::unique('photo_categories')->ignore($this->route('photo_category')->id),
            ],
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5000'
        ];
    }
}
