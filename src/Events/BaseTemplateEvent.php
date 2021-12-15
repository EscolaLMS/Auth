<?php

namespace EscolaLms\Auth\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseTemplateEvent
{
    use Dispatchable, SerializesModels;

    private Authenticatable $user;

    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }

    public function getUser(): Authenticatable
    {
        return $this->user;
    }
}
