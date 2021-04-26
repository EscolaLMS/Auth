<?php

namespace EscolaLms\Auth\Http\Requests\Admin;


class UserSettingsUpdateRequest extends AbstractUserIdInRouteRequest
{

    public function rules()
    {
        $rules = [
            'settings' => [
                'required',
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

        return array_merge(parent::rules(), $rules);
    }
}
