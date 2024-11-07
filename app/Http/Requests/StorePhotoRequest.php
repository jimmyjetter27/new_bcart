<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
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
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5000', //5mb
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'category' => 'nullable|array',
            'category.*' => 'nullable|exists:photo_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'images.*.required' => 'Please upload at least one image.',
            'images.*.image' => 'Each file must be a valid image.',
            'images.*.mimes' => 'Images must be of type: jpeg, png, jpg, gif, or svg.',
            'images.*.max' => 'Each image must be less than 5MB in size.',
            'category.*.exists' => 'The selected category is invalid.',
            'tags.*.string' => 'Each tag should be a valid string.',
            'tags.*.max' => 'Each tag should not exceed 255 characters.',
        ];
    }
}
