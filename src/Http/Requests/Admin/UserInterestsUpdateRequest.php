<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Categories\Models\Category;

class UserInterestsUpdateRequest extends AbstractUserIdInRouteRequest
{
    public function rules()
    {
        $rules = [
            'interests' => [
                'required',
                'array',
            ],
            'interests.*' => [
                'integer',
                'exists:' . Category::query()->getQuery()->from . ',id'
            ],
        ];

        return array_merge(parent::rules(), $rules);
    }
}
