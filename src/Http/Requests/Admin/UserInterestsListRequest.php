<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

class UserInterestsListRequest extends AbstractUserIdInRouteRequest
{
    public function authorize()
    {
        return $this->user()->can('view', $this->getRouteUser());
    }
}
