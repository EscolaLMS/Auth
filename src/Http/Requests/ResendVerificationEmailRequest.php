<?php

namespace EscolaLms\Auth\Http\Requests;

class ResendVerificationEmailRequest extends ExtendableRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'return_url' => ['nullable', 'url'],
        ];
    }
}
