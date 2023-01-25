<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Http\Requests\ExtendableRequest;
use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Rules\NoHtmlTags;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Validation\Rule;

class UserCreateRequest extends ExtendableRequest
{
    public function authorize()
    {
        return $this->user()->can('create', User::class);
    }

    public function rules()
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255', new NoHtmlTags()],
            'last_name' => ['required', 'string', 'max:255', new NoHtmlTags()],
            'roles' => ['sometimes', 'array'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'verified' => ['sometimes', 'boolean'],
            'password' => User::PASSWORD_RULES,
            'groups' => ['sometimes', 'array'],
            'groups.*' => ['integer', Rule::exists((new Group())->getTable(), (new Group())->getKeyName())],
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

        return array_merge($rules, ModelFields::getFieldsMetadataRules(User::class));
    }
}
