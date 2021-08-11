<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Auth\Models\UserSetting;
use Illuminate\Http\Resources\Json\JsonResource;
use EscolaLms\Auth\Traits\ResourceExtandable;

class UserSettingResource extends JsonResource
{
    use ResourceExtandable;
    
    public function __construct($resource)
    {
        assert($resource instanceof UserSetting);
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        /** @var UserSetting $resource */
        $resource = $this->resource;
        return [
            $resource->key => $resource->value
        ];
    }
}
