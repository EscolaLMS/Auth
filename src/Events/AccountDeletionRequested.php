<?php

namespace EscolaLms\Auth\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class AccountDeletionRequested extends Auth
{
    private string $returnUrl;

    public function __construct(Authenticatable $user,string $returnUrl)
    {
        parent::__construct($user);
        $this->returnUrl = $returnUrl;
    }

    public function getToken(): ?string
    {
        return $this->getUser()->delete_user_token ?? null;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
}
