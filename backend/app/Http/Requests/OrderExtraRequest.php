<?php

namespace App\Http\Requests;

use App\Models\Service;
use Closure;
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
            // Accepts either a numeric service id or a string slug so the
            // same endpoint works for mobile clients (id) and any future
            // slug-based integration. Existence is enforced in the closure.
            'service' => [
                'required',
                function (string $attribute, $value, Closure $fail) {
                    if (!is_string($value) && !is_numeric($value)) {
                        $fail(__('validation.required', ['attribute' => $attribute]));
                        return;
                    }

                    $exists = is_numeric($value)
                        ? Service::whereKey($value)->exists()
                        : Service::where('slug', $value)->exists();

                    if (!$exists) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                    }
                },
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'materials' => ['nullable', 'numeric'],
        ];
    }

    /**
     * Resolve the service model (by id or slug) so the controller does not
     * have to duplicate the lookup logic.
     */
    public function resolveService(): Service
    {
        $value = $this->input('service');

        return is_numeric($value)
            ? Service::findOrFail($value)
            : Service::where('slug', $value)->firstOrFail();
    }
}
