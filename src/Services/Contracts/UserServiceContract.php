<?php

namespace EscolaLms\Auth\Services\Contracts;

use EscolaLms\Core\Dtos\CriteriaDto;
use Illuminate\Contracts\Auth\Authenticatable as User;
use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Core\Dtos\PaginationDto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface UserServiceContract
{
    public function create(UserSaveDto $userSaveDto): ?User;

    public function update(User $user, UserSaveDto $userSaveDto): ?User;

    public function uploadAvatar(User $user, UploadedFile $avatar): ?string;

    public function deleteAvatar(User $user): bool;

    public function login(string $email, string $password): User;

    public function search(CriteriaDto $criteriaDto, PaginationDto $paginationDto): Collection;
}
