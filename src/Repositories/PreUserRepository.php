<?php

namespace EscolaLms\Auth\Repositories;

use EscolaLms\Auth\Models\PreUser;
use EscolaLms\Auth\Repositories\Contracts\PreUserRepositoryContract;
use EscolaLms\Core\Repositories\BaseRepository;

class PreUserRepository extends BaseRepository implements PreUserRepositoryContract
{
    public function model(): string
    {
        return PreUser::class;
    }

    public function getFieldsSearchable(): array
    {
        return [
            'token',
            'first_name',
            'last_name',
            'email',
        ];
    }

    public function findByToken(string $token): PreUser
    {
        /** @var PreUser */
        return $this->model->newQuery()
            ->where('token', $token)
            ->firstOrFail();
    }
}