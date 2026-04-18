<?php

namespace App\Http\Requests;

use App\Models\Highlight;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        $days = $this->warranty_days ?? 0;
        $months = ($this->warranty_months ?? 0) * 28;

        $this->merge([
            'total_warranty_days' => $days + $months,
            'highlights' => array_map(fn($h) => Highlight::make(['title' => $h]), $this->highlights),
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
            'price' => ['nullable', 'numeric'],
            'description.*' => ['nullable', 'string', 'max:500'],
            'image' => ['image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
            'category' => ['required', 'integer', 'exists:categories,id'],
            'warranty_days' => ['nullable', 'integer'],
            'warranty_months' => ['nullable', 'integer'],
            'highlights' => ['required', 'array', 'min:1'],
            'highlights.*' => ['required', 'string', 'max:255'],
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
