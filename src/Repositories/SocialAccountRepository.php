<?php

namespace EscolaLms\Auth\Repositories;

use EscolaLms\Auth\Models\SocialAccount;
use EscolaLms\Auth\Repositories\Contracts\SocialAccountRepositoryContract;
use EscolaLms\Core\Repositories\BaseRepository;

class SocialAccountRepository extends BaseRepository implements SocialAccountRepositoryContract
{
    public function model(): string
    {
        return SocialAccount::class;
    }

    public function getFieldsSearchable(): array
    {
        return [
            'user_id',
            'provider',
            'provider_id',
        ];
    }

    public function findByProviderAndProviderId(string $provider, string $providerId): ?SocialAccount
    {
        /** @var ?SocialAccount */
        return $this->model->newQuery()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }
}
