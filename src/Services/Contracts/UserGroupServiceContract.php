<?php

namespace EscolaLms\Auth\Services\Contracts;

use EscolaLms\Auth\Dtos\UserGroupDto;
use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Models\User;
use EscolaLms\Core\Dtos\CriteriaDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserGroupServiceContract
{
    public function create(UserGroupDto $userGroupDto): Group;

    public function update(Group $group, UserGroupDto $userGroupDto): Group;

    public function delete(Group $group): ?bool;

    public function searchAndPaginate(CriteriaDto $criteriaDto, array $appends = [], int $perPage = null, int $page = null): LengthAwarePaginator;

    public function getRegisterableGroups(): Collection;

    /**
     * Add member to group and return collection of members
     */
    public function addMember(Group $group, User $user): Collection;

    public function addMemberIfGroupIsRegisterable(Group $group, User $user): bool;

    /**
     * Remove member from group and return collection of members
     */
    public function removeMember(Group $group, User $user): Collection;
}
