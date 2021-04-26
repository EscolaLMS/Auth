<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

class UserGetRequest extends AbstractUserIdInRouteRequest
{
    public function rules()
    {
        // we want to return 404 for this request, not 422, so we ignore parent::rules()
        return [
            'id' => [
                'required'
            ]
        ];
    }
}
