<?php

namespace EscolaLms\Auth\Repositories\Criteria;

use EscolaLms\Auth\Models\Group;
use EscolaLms\Core\Repositories\Criteria\Criterion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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

            // to check mysql version
            if ($driver !== 'pgsql') {
                $version = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
                if (version_compare($version, '5.7.44') <= 0) {
                    $initialId = Group::min('id');
                    $allChild = DB::select("SELECT id, name, parent_id
                        FROM (SELECT * FROM groups
                              ORDER BY parent_id, id) AS sorted_groups,
                             (SELECT @pv := $initialId) AS init
                        WHERE FIND_IN_SET(parent_id, @pv)
                        AND LENGTH(@pv := CONCAT(@pv, ',', id))");
                    $ids = collect($allChild)->pluck('id')->toArray();
                    echo 'All child: ' . json_encode($allChild);
                    echo '$ids: ' . json_encode($ids);

                    if (count($ids) > 0) {
                        $groupIds = implode(',', $ids);
                        /** @var Collection $groups */
                        $groups = Group::query()->whereIn('id', $ids)->get();
                        $filteredGroups = $groups->filter(function (Group $group) {
                            return stripos($group->name_with_breadcrumbs, $this->value) !== false;
                        });

                        if (count($filteredGroups) > 0) {
                            $q->orWhereIn('id', $filteredGroups->pluck('id'));
                        }
                    }

                    return $q;
                }

            }

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

            return $q;
        });
    }
}
