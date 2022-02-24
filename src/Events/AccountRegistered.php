<?php

namespace EscolaLms\Auth\Events;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;

class AccountRegistered extends Registered
{
    public ?string $returnUrl;

    public function __construct(Authenticatable $user, ?string $returnUrl)
    {
        parent::__construct($user);
        $this->returnUrl = $returnUrl;
    }

    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }
}
