<?php

namespace EscolaLms\Auth\Listeners;

use EscolaLms\Auth\Events\AccountDeleted;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;

class EmailAnonymisation
{
    private UserServiceContract $userService;

    public function __construct(UserServiceContract $userService)
    {
        $this->userService = $userService;
    }

    public function handle(AccountDeleted $event): void
    {
        $this->userService->anonymiseEmail($event->getUser());
    }
}
