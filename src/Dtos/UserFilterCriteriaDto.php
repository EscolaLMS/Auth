<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Core\Repositories\Criteria\PeriodCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\DoesntHasCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\EqualCriterion;
use EscolaLms\Core\Repositories\Criteria\Primitives\HasCriterion;
use EscolaLms\Core\Repositories\Criteria\RoleCriterion;
use EscolaLms\Core\Repositories\Criteria\UserSearchCriterion;
use Carbon\Carbon;
use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use EscolaLms\Core\Dtos\CriteriaDto;

class UserFilterCriteriaDto extends CriteriaDto implements DtoContract, InstantiateFromRequest
{
    public static function instantiateFromRequest(Request $request): self
    {
        $criteria = new Collection();

        if ($request->get('search')) {
            $criteria->push(new UserSearchCriterion($request->get('search')));
        }

        if (!is_null($request->get('role'))) {
            $criteria->push(new RoleCriterion($request->get('role')));
        }

        if (!is_null($request->get('status'))) {
            $criteria->push(new EqualCriterion('is_active', $request->get('status')));
        }

        if (!is_null($request->get('onboarding'))) {
            $criteria->push(
                $request->get('onboarding') ? new HasCriterion('interests') : new DoesntHasCriterion('interests')
            );
        }

        if ($request->get('from') || $request->get('to')) {
            $criteria->push(new PeriodCriterion(new Carbon($request->get('from') ?? 0), new Carbon($request->get('to') ?? null)));
        }

        return new self($criteria);
    }
}
