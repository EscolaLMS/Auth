<?php

namespace EscolaLms\Auth\Http\Requests;

class SocialAuthRequest extends ExtendableRequest
{
    protected function prepareForValidation()
    {
        $this->merge(['provider' => $this->route('provider')]);
    }

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
            'provider' => ['required', 'string', 'in:facebook,google'],
        ];
    }
}
