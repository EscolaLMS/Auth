<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use Illuminate\Http\Request;

class UserUpdateAuthDataDto implements InstantiateFromRequest, DtoContract
{
    private string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public static function instantiateFromRequest(Request $request): self
    {
        return new self(
            $request->input('email'),
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->getEmail(),
        ];
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
