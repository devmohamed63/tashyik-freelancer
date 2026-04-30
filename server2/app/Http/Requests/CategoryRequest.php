<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
        $rules = [
            'name.*' => ['required', 'string', 'max:255'],
            'description.*' => ['nullable', 'string', 'max:500'],
            'image' => ['image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
            'parent' => ['nullable', 'integer', 'exists:categories,id'],
            'cities' => ['nullable', 'required_without:parent', 'array', 'min:1'],
            'cities.*' => ['nullable', 'integer', 'exists:cities,id'],
            'badge' => ['nullable', 'string', Rule::in(config('badges'))],
        ];

        if ($this->isMethod('PUT')) {
            // Update model request
            array_push($rules['image'], 'nullable');
        } else {
            // Store model request
            array_push($rules['image'], 'required');
        }

        return $rules;
    }
}
