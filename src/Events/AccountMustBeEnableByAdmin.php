<?php

namespace EscolaLms\Auth\Events;

use EscolaLms\Auth\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountMustBeEnableByAdmin
{
    use Dispatchable, SerializesModels;

    private User $user;
    private Authenticatable $registeredUser;

    public function __construct(User $user, Authenticatable $registeredUser)
    {
        $this->user = $user;
        $this->registeredUser = $registeredUser;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRegisteredUser(): Authenticatable
    {
        return $this->registeredUser;
    }
}
