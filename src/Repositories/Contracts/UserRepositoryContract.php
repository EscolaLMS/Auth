<?php

namespace EscolaLms\Auth\Repositories\Contracts;

use EscolaLms\Auth\Dtos\UserUpdateInterestsDto;
use EscolaLms\Auth\Dtos\UserUpdateSettingsDto;
use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryContract extends BaseRepositoryContract
{
    public function findByEmail(string $email): ?Authenticatable;

    public function findOrCreate(?int $id): Authenticatable;

    public function search(?string $query): LengthAwarePaginator;

    public function patchSettingsUsingDto(Authenticatable $user, UserUpdateSettingsDto $dto): Collection;
    public function putSettingsUsingDto(Authenticatable $user, UserUpdateSettingsDto $keysDto): Collection;
    public function updateSettings(Authenticatable $user, array $settings): Collection;

    public function addInterestById(Authenticatable $user, int $interest_id): Collection;
    public function removeInterestById(Authenticatable $user, int $interest_id): Collection;
    public function updateInterestsUsingDto(Authenticatable $user, UserUpdateInterestsDto $dto): Collection;
    public function updateInterests(Authenticatable $user, array $interests): Collection;

    public function updatePassword(Authenticatable $user, string $newPassword): bool;

    public function findByIdWithRelations(int $id, array $relations = []): ?Authenticatable;
}
