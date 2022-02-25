<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Auth\Models\UserSetting;
use Illuminate\Http\Resources\Json\JsonResource;
use EscolaLms\Auth\Traits\ResourceExtandable;

class UserSettingResource extends JsonResource
{
    use ResourceExtandable;

    public function __construct(UserSetting $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        /** @var UserSetting $resource */
        $resource = $this->resource;
        $fields = [
            $resource->key => $resource->value
        ];

        return self::apply($fields, $this);
    }
}
