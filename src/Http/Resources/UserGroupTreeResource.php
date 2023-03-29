<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Auth\Enums\AuthPermissionsEnum;

class UserGroupTreeResource extends UserGroupResource
{
    public function toArray($request): array
    {
        $children = $this->getResource()->children();
        if ($request->user()->can(AuthPermissionsEnum::USER_GROUP_LIST_SELF)) {
            $children->whereHas('users', fn($query) => $query->where('user_id', $request->user()->getKey()));
        }

        return array_merge(
            parent::toArray($request),
            [
                'subgroups' => self::collection($children->get())
            ]
        );
    }
}
