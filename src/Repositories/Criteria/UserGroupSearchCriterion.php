<?php

namespace EscolaLms\Auth\Repositories\Criteria;

use EscolaLms\Core\Repositories\Criteria\Criterion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserGroupSearchCriterion extends Criterion
{
    public function __construct($value = null)
    {
        parent::__construct(null, $value);
    }

    public function apply(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $like = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql' ? 'ILIKE' : 'LIKE';
            $q->where('name', $like, "%$this->value%");

            $fullNameIds = DB::select("
            WITH RECURSIVE group_hierarchy AS (
                SELECT id, name, parent_id, name::varchar AS full_name
                FROM groups
                WHERE parent_id IS NULL
                UNION ALL
                SELECT g.id, g.name, g.parent_id, CONCAT(gh.full_name, '. ', g.name) AS full_name
                FROM groups g
                INNER JOIN group_hierarchy gh ON g.parent_id = gh.id
                )
            SELECT * FROM group_hierarchy WHERE full_name like ?",
                ["%$this->value%"]
            );
            if (count($fullNameIds) > 0) {
                $q->orWhereIn('id', collect($fullNameIds)->pluck('id')->toArray());
            }
        });
    }
}
