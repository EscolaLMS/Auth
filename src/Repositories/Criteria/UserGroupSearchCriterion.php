<?php

namespace EscolaLms\Auth\Repositories\Criteria;

use EscolaLms\Auth\Models\Group;
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
            $driver = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $like = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

            $q->where('name', $like, "%$this->value%");

            return $this->searchNameWithBreadcrumbs($q, $driver, $like);
        });
    }

    private function searchNameWithBreadcrumbs(Builder $q, string $driver, string $like): Builder
    {
        // check mysql version
        if ($driver !== 'pgsql') {
            $version = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
            if (version_compare($version, '5.7.44') <= 0) {
                $groups = Group::query()->whereNotNull('parent_id')->get();

                $filteredGroups = $groups->filter(function (Group $group) {
                    return stripos($group->name_with_breadcrumbs, $this->value) !== false;
                });

                if (count($filteredGroups) > 0) {
                    $q->orWhereIn('id', $filteredGroups->pluck('id'));
                }

                return $q;
            }
        }

        $fullNameIds = DB::select("
            WITH RECURSIVE group_hierarchy AS (
                SELECT id, name, parent_id, CAST(name as VARCHAR(1000)) AS full_name
                FROM groups
                WHERE parent_id IS NULL
                UNION ALL
                SELECT g.id, g.name, g.parent_id, CONCAT(gh.full_name, '. ', g.name) AS full_name
                FROM groups g
                INNER JOIN group_hierarchy gh ON g.parent_id = gh.id
                )
            SELECT * FROM group_hierarchy WHERE full_name {$like} ?",
            ["%$this->value%"]
        );
        if (count($fullNameIds) > 0) {
            $q->orWhereIn('id', collect($fullNameIds)->pluck('id')->toArray());
        }

        return $q;
    }
}
