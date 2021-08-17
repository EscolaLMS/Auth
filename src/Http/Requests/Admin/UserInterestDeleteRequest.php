<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Categories\Models\Category;

class UserInterestDeleteRequest extends AbstractUserIdInRouteRequest
{
    public function authorize()
    {
        return $this->user()->can('updateInterests', $this->getRouteUser());
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();
        $this->merge([
            'interest_id' => $this->route('interest_id')
        ]);
    }

    public function rules()
    {
        $rules = [
            'interest_id' => [
                'required',
                'exists:' . Category::query()->getQuery()->from . ',id'
            ]
        ];

        return array_merge(parent::rules(), $rules);
    }
}
