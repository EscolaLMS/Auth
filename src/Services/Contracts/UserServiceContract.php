<?php

namespace EscolaLms\Auth\Services\Contracts;

use EscolaLms\Core\Dtos\CriteriaDto;
use Illuminate\Contracts\Auth\Authenticatable as User;
use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Dtos\UserUpdateDto;
use EscolaLms\Auth\Dtos\UserUpdateKeysDto;
use EscolaLms\Auth\Dtos\UserUpdateSettingsDto;
use EscolaLms\Core\Dtos\PaginationDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface UserServiceContract
{
    public function create(UserSaveDto $userSaveDto): ?User;

    public function createWithSettings(UserSaveDto $userSaveDto, UserUpdateSettingsDto $userSettingsDto): User;

    public function update(User $user, UserSaveDto $userSaveDto): ?User;

    public function patchUsingDto(UserUpdateDto $dto, UserUpdateKeysDto $keysDto, int $id): User;

    public function putUsingDto(UserUpdateDto $dto, int $id): User;

    public function uploadAvatar(User $user, UploadedFile $avatar): ?User;

    public function deleteAvatar(User $user): bool;

    public function login(string $email, string $password): User;

    public function search(CriteriaDto $criteriaDto, PaginationDto $paginationDto): Collection;

    public function searchAndPaginate(CriteriaDto $criteriaDto, array $appends = [], int $perPage = null, int $page = null): LengthAwarePaginator;
}
