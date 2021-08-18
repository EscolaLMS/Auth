<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

class UserDeleteRequest extends AbstractUserIdInRouteRequest
{
    public function authorize()
    {
        return $this->user()->can('delete', $this->getRouteUser());
    }
}
