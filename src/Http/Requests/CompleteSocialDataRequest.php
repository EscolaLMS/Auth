<?php

namespace EscolaLms\Auth\Http\Requests;

class CompleteSocialDataRequest extends ExtendableRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['token' => $this->route('token')]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'token' => ['required', 'string', 'max:255', 'exists:pre_users,token'],
            'return_url' => ['sometimes', 'url'],
        ];
    }
}
