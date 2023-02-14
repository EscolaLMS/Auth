<?php

namespace EscolaLms\Auth\Repositories\Contracts;

use EscolaLms\Auth\Models\SocialAccount;
use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;

interface SocialAccountRepositoryContract extends BaseRepositoryContract
{
    public function findByProviderAndProviderId(string $provider, string $providerId): ?SocialAccount;
}
