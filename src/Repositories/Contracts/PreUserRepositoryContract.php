<?php

namespace EscolaLms\Auth\Repositories\Contracts;

use EscolaLms\Auth\Models\PreUser;
use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;

interface PreUserRepositoryContract extends BaseRepositoryContract
{
    public function findByToken(string $token): ?PreUser;
}
