<?php

namespace EscolaLms\Auth\Http\Requests;

use EscolaLms\Auth\Enums\GenderType;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Rules\AdditionaFieldRules;
use EscolaLms\ModelFields\Facades\ModelFields;

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
        $rules = [
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'age' => ['numeric'],
            'gender' => ['in:' . implode(',', GenderType::getValues())],
        ];
        $additional_fields = array_map(fn (array $rule) => ['sometimes'] + $rule, ModelFields::getFieldsMetadataRules(User::class));

        return array_merge($rules, $additional_fields);
    }
}
