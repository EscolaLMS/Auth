<?php

namespace EscolaLms\Auth\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class ForgotPassword extends Auth
{
    private string $returnUrl;

    public function __construct(Authenticatable $user, string $returnUrl)
    {
        $this->returnUrl = $returnUrl;
        parent::__construct($user);
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
}
