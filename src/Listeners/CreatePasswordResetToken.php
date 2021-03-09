<?php

namespace EscolaLms\Auth\Listeners;

use EscolaLms\Auth\Events\PasswordForgotten;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Support\Str;

class CreatePasswordResetToken
{
    private UserRepositoryContract $userRepository;

    public function __construct(UserRepositoryContract $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(PasswordForgotten $event): void
    {
        $user = $event->getUser();

        $this->userRepository->update([
            'password_reset_token' => Str::random(32),
        ], $user->getKey());

        $user->refresh();

        $url = $event->getReturnUrl() .
            '?email=' . $user->email .
            '&token=' . $user->password_reset_token;

        $user->notify(new PasswordForgotten($user, $url));
    }
}
