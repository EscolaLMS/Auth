<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Auth\Http\Requests\Admin\UserInterestsUpdateRequest;
use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Ramsey\Collection\Set;

class UserUpdateInterestsDto implements InstantiateFromRequest, DtoContract
{
    private Set $interests;

    public function __construct(Set $interests)
    {
        if (!in_array($interests->getType(), ['int', 'integer'])) {
            throw new InvalidArgumentException("Interests must be a Set of integer values");
        }
        $this->interests = $interests;
    }

    public static function instantiateFromRequest(Request $request): self
    {
        assert($request instanceof UserInterestsUpdateRequest);
        $set = new Set('int', $request->input('interests', []));
        return new self($set);
    }

    public function toArray(): array
    {
        return $this->interests->toArray();
    }

    public function getInterests(): Set
    {
        return $this->interests;
    }
}
