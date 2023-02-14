<?php

namespace EscolaLms\Auth\Http\Requests;

class SocialAuthRequest extends ExtendableRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['provider' => $this->route('provider')]);
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', 'in:facebook,google'],
            'return_url' => ['sometimes', 'url', 'nullable'],
        ];
    }
}
