<?php

namespace EscolaLms\Auth\Models\Traits;

use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Models\GroupUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read Collection $groups
 */
trait HasGroups
{

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)->using(GroupUser::class);
    }

    public function belongsToGroup(Group $group): bool
    {
        return $this->relationLoaded('groups')
            ? $this->groups->contains('id', '=', $group->getKey())
            : $this->groups()->wherePivot('group_id', $group->getKey())->exists();
    }
}
