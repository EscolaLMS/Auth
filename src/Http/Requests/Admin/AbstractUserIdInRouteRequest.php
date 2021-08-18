<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Models\User;

abstract class AbstractUserIdInRouteRequest extends AbstractAdminOnlyRequest
{
    protected function prepareForValidation()
    {
        parent::prepareForValidation();
        $this->merge(['id' => $this->route('id')]);
    }

    public function rules()
    {
        return [
            'id' => [
                'required',
                'exists:' . User::query()->getQuery()->from . ',id'
            ]
        ];
    }

    public function getRouteUser(): User
    {
        return User::query()->findOrFail($this->route('id'));
    }
}
