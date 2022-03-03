<?php

namespace EscolaLms\Auth\Http\Requests;

use EscolaLms\Auth\Models\User;

class LoginRequest extends ExtendableRequest
{
    public function authorize(): bool
    {
        return empty($this->user());
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => User::PASSWORD_RULES,
            'remember_me' => ['boolean'],
        ];
    }
}
