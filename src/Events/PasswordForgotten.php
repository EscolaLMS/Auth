<?php

namespace EscolaLms\Auth\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PasswordForgotten
{
    use Dispatchable, SerializesModels;

    private Authenticatable $user;
    private string $returnUrl;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Authenticatable $user, string $returnUrl)
    {
        $this->user = $user;
        $this->returnUrl = $returnUrl;
    }

    public function getUser(): Authenticatable
    {
        return $this->user;
    }

    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }
}
