<?php

namespace EscolaLms\Auth\Services;


use EscolaLms\Auth\Dtos\UserGroupDto;
use EscolaLms\Auth\Models\Group;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Repositories\Contracts\UserGroupRepositoryContract;
use EscolaLms\Auth\Services\Contracts\UserGroupServiceContract;
use EscolaLms\Core\Dtos\CriteriaDto;
use EscolaLms\Core\Repositories\Criteria\Primitives\EqualCriterion;
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

    public function addMemberToMultipleGroups(array $groups, User $user): void
    {
        foreach ($groups as $group) {
            if (is_numeric($group)) {
                $group = $this->userGroupRepository->find($group);
            }
            if ($group instanceof Group) {
                $this->addMember($group, $user);
            }
        }
    }

    public function registerMemberToMultipleGroups(array $groups, User $user): void
    {
        foreach ($groups as $group) {
            if (is_numeric($group)) {
                $group = $this->userGroupRepository->find($group);
            }
            if ($group instanceof Group) {
                $this->addMemberIfGroupIsRegisterable($group, $user);
            }
        }
    }

    public function addMemberIfGroupIsRegisterable(Group $group, User $user): bool
    {
        if ($group->registerable) {
            $this->addMember($group, $user);
            return true;
        }
        return false;
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

    public function getRegisterableGroups(): Collection
    {
        return $this->userGroupRepository->queryWithAppliedCriteria([new EqualCriterion('registerable', true)])->get();
    }
}
