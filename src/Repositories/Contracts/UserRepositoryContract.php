<?php

namespace EscolaLms\Auth\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryContract extends BaseRepositoryContract
{
    public function findByEmail(string $email): ?Authenticatable;

    public function findOrCreate(?int $id): Authenticatable;

    public function search(?string $query): LengthAwarePaginator;

    public function updateSettings(Authenticatable $user, array $settings): void;

    public function updatePassword(Authenticatable $user, string $newPassword): bool;
}
