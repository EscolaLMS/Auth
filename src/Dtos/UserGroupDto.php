<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use Illuminate\Http\Request;

class UserGroupDto implements InstantiateFromRequest, DtoContract
{
    private string $name;
    private bool $registerable;
    private ?int $parent_id;

    public function __construct(string $name, bool $registerable = false, ?int $parent_id = null)
    {
        $this->name = $name;
        $this->registerable = $registerable;
        $this->parent_id = $parent_id;
    }

    public static function instantiateFromRequest(Request $request): self
    {
        return new self(
            $request->input('name'),
            $request->input('registerable', false),
            $request->input('parent_id'),
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRegisterable()
    {
        return $this->registerable;
    }

    public function getParentId(): ?int
    {
        return $this->parent_id;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'registerable' => $this->getRegisterable(),
            'parent_id' => $this->getParentId(),
        ];
    }
}
