<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Rules\NoHtmlTags;
use EscolaLms\ModelFields\Facades\ModelFields;
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
            'first_name' => [$this->requiredIfPut(), 'string', 'max:255', new NoHtmlTags()],
            'last_name' => [$this->requiredIfPut(), 'string', 'max:255', new NoHtmlTags()],
            // email, password and roles are optional even when using Put
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::query()->getQuery()->from)->ignore($this->route('id'))
            ],
            'email_verified' => [
                'sometimes',
                'boolean'
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

        $additionalFields = array_map(fn (array $rule) => array_merge(['sometimes'], $rule), ModelFields::getFieldsMetadataRules(User::class));

        return array_merge(parent::rules(), $rules, $additionalFields);
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
