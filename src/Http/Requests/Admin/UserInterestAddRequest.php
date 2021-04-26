<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Categories\Models\Category;

class UserInterestAddRequest extends AbstractUserIdInRouteRequest
{
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
