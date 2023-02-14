<?php

namespace EscolaLms\Auth\Listeners;

use EscolaLms\Auth\Events\AccountDeleted;
use EscolaLms\Auth\Repositories\Contracts\SocialAccountRepositoryContract;

class RemoveUserSocialAccounts
{
    private SocialAccountRepositoryContract $socialAccountRepository;

    public function __construct(SocialAccountRepositoryContract $socialAccountRepository)
    {
        $this->socialAccountRepository = $socialAccountRepository;
    }

    public function handle(AccountDeleted $event): void
    {
        $this->socialAccountRepository->deleteWhere([
            'user_id' => $event->getUser()->getKey(),
        ]);
    }
}
