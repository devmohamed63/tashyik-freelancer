<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $uuid = $this->input('conversation_uuid');
        if ($uuid !== null && is_string($uuid) && trim($uuid) === '') {
            $this->merge(['conversation_uuid' => null]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000'],
            'guest_token' => ['nullable', 'string', 'max:255'],
            'conversation_uuid' => ['nullable', 'string', 'uuid'],
        ];
    }
}
