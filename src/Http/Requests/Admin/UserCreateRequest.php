<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Http\Requests\ExtendableRequest;
use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Models\User;
use Illuminate\Validation\Rule;

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
            'password' => User::PASSWORD_RULES,
            'group_id' => ['sometimes', 'integer', Rule::exists((new Group())->getTable(), (new Group())->getKeyName())],
            'settings' => [
                'sometimes',
                'array'
            ],
            'settings.*' => [
                'array'
            ],
            'settings.*.key' => [
                'required',
                'string',
            ],
            'settings.*.value' => [
                'required',
                'nullable',
                'string',
            ],
        ];
    }

    public function getGroup(): ?Group
    {
        if ($this->has('group_id')) {
            return Group::find($this->input('group_id'));
        }
        return null;
    }
}
