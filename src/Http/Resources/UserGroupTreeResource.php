<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Auth\Http\Resources\UserGroupResource;

class UserGroupTreeResource extends UserGroupResource
{
    public function toArray($request)
    {
        return array_merge(
            parent::toArray($request),
            [
                'subgroups' => self::collection($this->getResource()->children)
            ]
        );
    }
}
