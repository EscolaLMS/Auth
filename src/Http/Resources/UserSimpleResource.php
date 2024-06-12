<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Auth\Traits\ResourceExtandable;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSimpleResource extends JsonResource
{
    use ResourceExtandable;

    public function toArray($request): array
    {
        $fields = [
            'id' => $this->resource->getKey(),
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'email' => $this->resource->email,
        ];

        return self::apply($fields, $this);
    }
}
