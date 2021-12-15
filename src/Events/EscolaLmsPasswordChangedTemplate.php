<?php

namespace EscolaLms\Auth\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EscolaLmsPasswordChangedTemplate
{
    use Dispatchable, SerializesModels;

    private Authenticatable $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }

    public function getUser(): Authenticatable
    {
        return $this->user;
    }
}
