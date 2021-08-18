<?php

namespace EscolaLms\Auth\Http\Requests;

use EscolaLms\Auth\Enums\GenderType;
use EscolaLms\Auth\Http\Requests\ExtendableRequest;

class ProfileUpdateRequest extends ExtendableRequest
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

    public function rules()
    {
        return [
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'age' => ['numeric'],
            'gender' => ['in:' . implode(',', GenderType::getValues())],
        ];
    }
}
