<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'roles' => __('ui.role')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' =>  ['required', 'string', 'max:255'],
            'phone' =>  ['required', 'string', 'max:255'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'image' => ['image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
            'password' => [Password::defaults()],
        ];

        if ($this->isMethod('PUT')) {
            // Update model request
            array_push($rules['image'], 'nullable');
            array_push($rules['password'], 'nullable');

            // Service provider fields (all optional on update)
            $rules['status'] = ['nullable', 'string', 'in:pending,active,inactive'];
            $rules['entity_type'] = ['nullable', 'string', 'in:individual,institution,company'];
            $rules['city_id'] = ['nullable', 'integer', 'exists:cities,id'];
            $rules['bank_name'] = ['nullable', 'string', 'max:255'];
            $rules['iban'] = ['nullable', 'string', 'max:255'];
            $rules['residence_name'] = ['nullable', 'string', 'max:255'];
            $rules['residence_number'] = ['nullable', 'string', 'max:255'];
            $rules['commercial_registration_number'] = ['nullable', 'string', 'max:255'];
            $rules['tax_registration_number'] = ['nullable', 'string', 'max:255'];
            $rules['balance'] = ['nullable', 'numeric', 'min:0'];
            $rules['categories'] = ['nullable', 'array'];
            $rules['categories.*'] = ['integer', 'exists:categories,id'];
            $rules['residence_image'] = ['nullable', 'image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')];
            $rules['commercial_registration_image'] = ['nullable', 'image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')];
        } else {
            // Store model request
            array_push($rules['image'], 'required');
            array_push($rules['password'], 'required');
        }

        return $rules;
    }
}
