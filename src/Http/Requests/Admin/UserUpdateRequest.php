<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Models\User;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends AbstractUserIdInRouteRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->getRouteUser());
    }

    public function rules()
    {
        $rules = [
            'first_name' => [$this->requiredIfPut(), 'string', 'max:255'],
            'last_name' => [$this->requiredIfPut(), 'string', 'max:255'],
            // email, password and roles are optional even when using Put
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::query()->getQuery()->from)->ignore($this->route('id'))
            ],
            'password' => [
                'sometimes',
                ...User::PASSWORD_RULES
            ],
            'roles' => [
                'sometimes',
                'array'
            ]
        ];
        return array_merge(parent::rules(), $rules);
    }

    private function requiredIfPut()
    {
        if ($this->getMethod() === 'PUT') {
            return 'required';
        } else {
            return 'sometimes';
        }
    }
}
