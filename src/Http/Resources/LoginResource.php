<?php

namespace EscolaLms\Auth\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'token' => $this->accessToken,
            'expires_at' => $this->token->expires_at,
        ];
    }
}
