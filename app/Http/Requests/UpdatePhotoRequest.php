<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhotoRequest extends FormRequest
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
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5000',
            'category' => 'nullable|array',
            'category.*' => 'nullable|exists:photo_categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'image.image' => 'The file uploaded should be a valid image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, or svg.',
            'image.max' => 'The image must be less than 5MB in size.',
            'title.string' => 'The title should be a valid string.',
            'title.max' => 'The title should not exceed 255 characters.',
            'description.string' => 'The description should be a valid string.',
            'price.numeric' => 'The price must be a number.',
            'category.*.exists' => 'One of the selected categories is invalid.',
            'tags.*.string' => 'Each tag should be a valid string.',
            'tags.*.max' => 'Each tag should not exceed 255 characters.',
        ];
    }
}
