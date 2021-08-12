<?php

namespace EscolaLms\Auth\Http\Requests;

use EscolaLms\Auth\Enums\GenderType;
use Illuminate\Foundation\Http\FormRequest;
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
        return (bool)$this->user();
    }
}

ProfileUpdateRequest::extendRules([
    'first_name' => ['string', 'max:255'],
    'last_name' => ['string', 'max:255'],
    'age' => ['numeric'],
    'gender' => ['in:' . implode(',', GenderType::getValues())],
]);
