<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Exceptions\UserNotFoundException;
use EscolaLms\Auth\Http\Requests\ExtendableRequest;
use EscolaLms\Auth\Models\User;

abstract class AbstractUserIdInRouteRequest extends ExtendableRequest
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
        /** @var User $user */
        $user = User::query()->find($this->route('id'));

        if (is_null($user)) {
            throw new UserNotFoundException();
        }

        return $user;
    }
}
