<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Auth\Enums\AuthPermissionsEnum;
use EscolaLms\Auth\Traits\ResourceExtandable;

class UserGroupTreeResource extends UserGroupResource
{
    use ResourceExtandable;

    public function toArray($request): array
    {
        $children = $this->getResource()->children();
        if (
            $request->user()->can(AuthPermissionsEnum::USER_GROUP_LIST_SELF)
            && !$request->user()->can(AuthPermissionsEnum::USER_GROUP_LIST)
        ) {
            $children->whereHas('users', fn($query) => $query->where('user_id', $request->user()->getKey()));
        }

        $fields = array_merge(
            parent::toArray($request),
            [
                'subgroups' => self::collection($children->get())
            ]
        );

        return self::apply($fields, $this);
    }
}
