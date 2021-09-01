<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

class UserGroupDeleteRequest extends AbstractGroupIdInRouteRequest
{
    public function authorize()
    {
        return $this->user()->can('delete', $this->getGroupFromRoute());
    }
}
