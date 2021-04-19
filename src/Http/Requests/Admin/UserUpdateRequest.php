<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

class UserUpdateRequest extends AbstractUserIdInRouteRequest
{
    public function rules()
    {
        $rules = [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['sometimes', 'string', 'min:6'],
        ];
        return array_merge(parent::rules(), $rules);
    }
}
