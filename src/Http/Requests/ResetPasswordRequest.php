<?php

namespace EscolaLms\Auth\Http\Requests;

use EscolaLms\Auth\Models\User;

class ResetPasswordRequest extends ExtendableRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'exists:users,email'],
            'token' => ['required', 'string'],
            'password' => User::PASSWORD_RULES,
        ];
    }
}
