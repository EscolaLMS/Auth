<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Http\Requests\ExtendableRequest;
use EscolaLms\Auth\Models\User;

class UserCreateRequest extends ExtendableRequest
{
    public function authorize()
    {
        return $this->user()->can('create', User::class);
    }

    public function rules()
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'roles' => ['sometimes', 'array'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'verified' => ['sometimes', 'boolean'],
            'password' => User::PASSWORD_RULES
        ];
    }
}
