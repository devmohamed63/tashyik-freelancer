<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderExtraRequest extends FormRequest
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
            'order' => ['required', 'integer', 'exists:orders,id'],
            'service' => ['required', 'integer', 'exists:services,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'materials' => ['nullable', 'numeric'],
        ];
    }
}
