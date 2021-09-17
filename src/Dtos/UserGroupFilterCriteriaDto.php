<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Auth\Repositories\Criteria\UserGroupRootCriterion;
use EscolaLms\Auth\Repositories\Criteria\UserGroupSearchCriterion;
use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use EscolaLms\Core\Dtos\CriteriaDto;
use EscolaLms\Core\Repositories\Criteria\Primitives\EqualCriterion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UserGroupFilterCriteriaDto extends CriteriaDto implements DtoContract, InstantiateFromRequest
{
    public static function instantiateFromRequest(Request $request, bool $tree = false): self
    {
        $criteria = new Collection();

        if ($request->has('search')) {
            $criteria->push(new UserGroupSearchCriterion($request->get('search')));
        }
        if ($request->has('parent_id')) {
            $criteria->push(new EqualCriterion('parent_id', $request->get('parent_id')));
        }
        if ($criteria->isEmpty() && $tree) {
            $criteria->push(new UserGroupRootCriterion());
        }

        return new self($criteria);
    }
}
