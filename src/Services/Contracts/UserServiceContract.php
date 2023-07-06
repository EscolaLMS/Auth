<?php

namespace EscolaLms\Auth\Services\Contracts;

use EscolaLms\Auth\Models\User as AuthUser;
use EscolaLms\Core\Dtos\CriteriaDto;
use EscolaLms\Core\Dtos\OrderDto;
use Illuminate\Contracts\Auth\Authenticatable as User;
use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Dtos\UserUpdateDto;
use EscolaLms\Auth\Dtos\UserUpdateKeysDto;
use EscolaLms\Auth\Dtos\UserUpdateSettingsDto;
use EscolaLms\Core\Dtos\PaginationDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface UserServiceContract
{
    public function create(UserSaveDto $userSaveDto): ?User;

    public function createWithSettings(UserSaveDto $userSaveDto, UserUpdateSettingsDto $userSettingsDto): User;

    public function update(User $user, UserSaveDto $userSaveDto): ?User;

    public function patchUsingDto(UserUpdateDto $dto, UserUpdateKeysDto $keysDto, int $id): User;

    public function putUsingDto(UserUpdateDto $dto, int $id): User;

    public function uploadAvatar(User $user, $avatar): ?User;

    public function deleteAvatar(User $user): bool;

    public function login(string $email, string $password): User;

    public function search(CriteriaDto $criteriaDto, PaginationDto $paginationDto): Collection;

    public function searchAndPaginate(
        CriteriaDto $criteriaDto,
        ?array $columns = [],
        ?array $with = [],
        ?array $appends = [],
        ?int $perPage = null,
        ?int $page = null,
        ?OrderDto $orderDto = null
    ): LengthAwarePaginator;

    public function updateAdditionalFieldsFromRequest(User $user, FormRequest $request): void;

    public function anonymiseEmail(AuthUser $user): void;

    /**
     * @deprecated
     */
    public function assignableUsers(string $assignableBy, ?int $perPage = null, ?int $page = null): LengthAwarePaginator;
    public function assignableUsersWithCriteria(CriteriaDto $dto, ?int $perPage = null, ?int $page = null): LengthAwarePaginator;

    public function initProfileDeletion(User $user, string $returnUrl): void;
    public function confirmDeletionProfile(int $userId, string $hash): void;

}
