<?php

namespace EscolaLms\Auth\Repositories\Criteria\AdditionalField;

use EscolaLms\Core\Repositories\Criteria\Criterion;
use Illuminate\Database\Eloquent\Builder;

class AdditionalFieldLikeCriterion extends Criterion
{
    public function apply(Builder $query): Builder
    {
        return $query->whereHas('fields', function ($query) {
            return $query->where([
                ['name', '=', $this->key],
                ['value', 'LIKE', "%$this->value%"],
            ]);
        });
    }
}
