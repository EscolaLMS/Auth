<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

class UserAvatarDeleteRequest extends AbstractUserIdInRouteRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->getRouteUser());
    }
}
