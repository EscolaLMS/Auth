<?php

namespace EscolaLms\Auth\Services;

use EscolaLms\Auth\Dtos\UserGroupDto;
use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Repositories\Contracts\UserGroupRepositoryContract;
use EscolaLms\Auth\Services\Contracts\UserGroupServiceContract;
use EscolaLms\Core\Dtos\CriteriaDto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserGroupService implements UserGroupServiceContract
{
    private UserGroupRepositoryContract $userGroupRepository;

    public function __construct(UserGroupRepositoryContract $userGroupRepository)
    {
        $this->userGroupRepository = $userGroupRepository;
    }

    public function create(UserGroupDto $userGroupDto): Group
    {
        return $this->userGroupRepository->create($userGroupDto->toArray());
    }

    public function update(Group $group, UserGroupDto $userGroupDto): Group
    {
        $this->userGroupRepository->update($userGroupDto->toArray(), $group->id);
        return $group->refresh();
    }

    public function delete(Group $group): ?bool
    {
        return $this->userGroupRepository->delete($group->getKey());
    }

    public function addMember(Group $group, User $user): Collection
    {
        $group->users()->attach($user->getKey());
        return $group->refresh()->users;
    }

    public function removeMember(Group $group, User $user): Collection
    {
        $group->users()->detach($user->getKey());
        return $group->refresh()->users;
    }

    public function searchAndPaginate(CriteriaDto $criteriaDto, array $appends = [], int $perPage = null, int $page = null): LengthAwarePaginator
    {
        return $this->userGroupRepository->queryWithAppliedCriteria($criteriaDto->toArray())->paginate($perPage, ['*'], 'page', $page)->appends($appends);
    }
}
