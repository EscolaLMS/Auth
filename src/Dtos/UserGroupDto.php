<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use Illuminate\Http\Request;

class UserGroupDto implements InstantiateFromRequest, DtoContract
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function instantiateFromRequest(Request $request): self
    {
        return new self(
            $request->input('name'),
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
        ];
    }
}
