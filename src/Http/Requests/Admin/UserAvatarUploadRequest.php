<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Http\Requests\Admin\AbstractUserIdInRouteRequest;

class UserAvatarUploadRequest extends AbstractUserIdInRouteRequest
{
    public function rules()
    {
        $rules = [
            'avatar' => ['required', 'file', 'mimes:png,jpg,jpeg,svg,gif'],
        ];
        return array_merge(parent::rules(), $rules);
    }
}
