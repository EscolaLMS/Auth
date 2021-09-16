<?php

namespace EscolaLms\Auth\Http\Requests;

use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Models\User;
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
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [...User::PASSWORD_RULES, 'confirmed'],
            'verified' => ['prohibited'],
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
