<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class BasicInformationRequest extends FormRequest
{
    /**
     * The URI that users should be redirected to if validation fails.
     *
     * @var string
     */
    protected $redirect = '';

    public function __construct()
    {
        $this->redirect = route('dashboard.settings.index', ['tab' => 'basic-information']);
    }

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
        $stringRules = ['required', 'string', 'max:255'];

        return [
            'name.*' => [...$stringRules],
            'description.*' => [...$stringRules],
            'logo' => ['nullable', 'image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
            'icon' => ['nullable', 'image', 'mimes:' . config('app.allowed_image_mimes'), 'max:' . config('app.upload_max_size')],
            'phone_number' => ['required', 'string', 'max:255'],
            'whatsapp_link' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255'],
        ];
    }
}
