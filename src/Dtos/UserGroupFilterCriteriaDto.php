<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Auth\Enums\AuthPermissionsEnum;
use EscolaLms\Auth\Repositories\Criteria\UserGroupRootCriterion;
use EscolaLms\Auth\Repositories\Criteria\UserGroupSearchCriterion;
use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use EscolaLms\Core\Dtos\CriteriaDto;
use EscolaLms\Core\Repositories\Criteria\Primitives\EqualCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\HasCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\InCriterion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UserGroupFilterCriteriaDto extends CriteriaDto implements DtoContract, InstantiateFromRequest
{
    public static function instantiateFromRequest(Request $request, bool $tree = false): self
    {
        $criteria = new Collection();

        if (
            $request->user()->can(AuthPermissionsEnum::USER_GROUP_LIST_SELF)
            && !$request->user()->can(AuthPermissionsEnum::USER_GROUP_LIST)
        ) {
            $criteria->push(
                new HasCriterion('users', fn ($query) => $query->where('user_id', $request->user()->getKey()))
            );
        }
        if ($request->has('search')) {
            $criteria->push(new UserGroupSearchCriterion($request->get('search')));
        }
        if ($request->has('parent_id')) {
            $criteria->push(new EqualCriterion('parent_id', $request->get('parent_id')));
        }
        if ($request->has('user_id')) {
            $criteria->push(new HasCriterion('users', fn ($query) => $query->where('user_id', $request->get('user_id'))));
        }
        if ($request->has('id')) {
            $criteria->push(new InCriterion('id', $request->get('id', [])));
        }
        if ($criteria->isEmpty() && $tree) {
            $criteria->push(new UserGroupRootCriterion());
        }

        return new self($criteria);
    }
}
