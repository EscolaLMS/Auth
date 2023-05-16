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
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
        ];

        return self::apply($fields, $this);
    }
}
