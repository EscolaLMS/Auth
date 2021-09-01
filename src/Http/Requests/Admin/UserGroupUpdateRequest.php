<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

class UserGroupUpdateRequest extends AbstractGroupIdInRouteRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->getGroupFromRoute());
    }
}
