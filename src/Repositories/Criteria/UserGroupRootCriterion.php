<?php

namespace EscolaLms\Auth\Repositories\Criteria;

use EscolaLms\Core\Repositories\Criteria\Criterion;
use Illuminate\Database\Eloquent\Builder;

class UserGroupRootCriterion extends Criterion
{
    public function __construct()
    {
        parent::__construct(null, null, null);
    }

    public function apply(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }
}
