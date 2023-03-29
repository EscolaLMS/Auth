<?php

namespace EscolaLms\Auth\Repositories\Criteria;

use EscolaLms\Core\Repositories\Criteria\Criterion;
use Illuminate\Database\Eloquent\Builder;

class AssignableByCriterion extends Criterion
{
    public function __construct(string $value)
    {
        parent::__construct(null, $value);
    }

    public function apply(Builder $query): Builder
    {
        return $query
            ->whereHas(
                'roles',
                fn(Builder $query) => $query
                    ->whereHas(
                        'permissions',
                        fn(Builder $query) => $query->where('permissions.name', '=', $this->value)
                    )
                );
    }
}
