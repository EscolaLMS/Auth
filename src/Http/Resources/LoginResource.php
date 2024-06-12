<?php

namespace EscolaLms\Auth\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'token' => $this->resource->accessToken,
            'expires_at' => $this->resource->token->expires_at,
        ];
    }
}
