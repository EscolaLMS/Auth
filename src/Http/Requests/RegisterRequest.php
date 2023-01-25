<?php

namespace EscolaLms\Auth\Http\Requests;

use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Rules\NoHtmlTags;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Validation\Rule;

class RegisterRequest extends ExtendableRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return empty($this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules =  [
            'first_name' => ['required', 'string', 'max:255', new NoHtmlTags()],
            'last_name' => ['required', 'string', 'max:255', new NoHtmlTags()],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [...User::PASSWORD_RULES, 'confirmed'],
            'verified' => ['prohibited'],
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
            'roles' => ['prohibited'],
            'return_url' => ['required', 'url'],
        ];

        return array_merge($rules, ModelFields::getFieldsMetadataRules(User::class));
    }
}
