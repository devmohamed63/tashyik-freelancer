<?php

namespace App\Http\Requests;

use App\Models\Banner;
use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->status
                ? Banner::ACTIVE_STATUS
                : Banner::INACTIVE_STATUS
        ]);
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
            'url' => ['nullable', 'url', 'string', 'max:255'],
            'status' => ['nullable'],
            'image' => ['image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
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
