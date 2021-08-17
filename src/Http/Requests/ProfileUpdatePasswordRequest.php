<?php

namespace EscolaLms\Auth\Http\Requests;

use EscolaLms\Auth\Rules\MatchOldPassword;

class ProfileUpdatePasswordRequest extends ExtendableRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required', 'min:6'],
            'new_confirm_password' => ['same:new_password'],
        ];
    }
}
