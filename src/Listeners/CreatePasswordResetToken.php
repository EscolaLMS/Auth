<?php

namespace EscolaLms\Auth\Listeners;

use EscolaLms\Auth\Events\EscolaLmsForgotPasswordTemplateEvent;
use EscolaLms\Auth\Notifications\ResetPassword;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Support\Str;

class CreatePasswordResetToken
{
    private UserRepositoryContract $userRepository;

    public function __construct(UserRepositoryContract $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(EscolaLmsForgotPasswordTemplateEvent $event): void
    {
        $user = $event->getUser();

        $this->userRepository->update([
            'password_reset_token' => Str::random(32),
        ], $user->getKey());

        $user->refresh();

        $user->notify(new ResetPassword($user->password_reset_token, $event->getReturnUrl()));
    }
}
