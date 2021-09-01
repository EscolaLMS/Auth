<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Models\User;

class UserGroupMemberAddRequest extends AbstractGroupIdInRouteRequest
{
    public function authorize()
    {
        return $this->user()->can('addMember', $this->getGroupFromRoute());
    }

    public function getUserFromInput(): User
    {
        return User::query()->findOrFail($this->input('user_id'));
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            'user_id' => [
                'required',
                'exists:' . User::query()->getQuery()->from . ',id'
            ]
        ]);
    }
}
