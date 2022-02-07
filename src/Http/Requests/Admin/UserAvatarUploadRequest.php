<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Files\Rules\FileOrStringRule;

class UserAvatarUploadRequest extends AbstractUserIdInRouteRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->getRouteUser());
    }

    public function rules(): array
    {
        $pathPrefix = 'avatars/' . $this->route('id');

        $rules = [
            'avatar' => ['required', new FileOrStringRule(['file', 'mimes:png,jpg,jpeg,svg,gif'], $pathPrefix)],
        ];
        return array_merge(parent::rules(), $rules);
    }
}
