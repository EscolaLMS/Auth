<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use EscolaLms\Core\Dtos\CriteriaDto;
use EscolaLms\Core\Repositories\Criteria\UserGroupSearchCriterion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UserGroupFilterCriteriaDto extends CriteriaDto implements DtoContract, InstantiateFromRequest
{
    public static function instantiateFromRequest(Request $request): self
    {
        $criteria = new Collection();

        if ($request->get('search')) {
            $criteria->push(new UserGroupSearchCriterion($request->get('search')));
        }

        return new self($criteria);
    }
}
