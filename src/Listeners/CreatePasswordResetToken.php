<?php

namespace EscolaLms\Auth\Listeners;

use EscolaLms\Auth\Events\ForgotPassword;
use EscolaLms\Auth\Notifications\ResetPassword;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Support\Str;

class CreatePasswordResetToken
{
    private UserRepositoryContract $userRepository;
    private static $runEventForgotPassword;

    public function __construct(UserRepositoryContract $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(ForgotPassword $event): void
    {
        if (!is_callable(self::getRunEventForgotPassword()) || self::getRunEventForgotPassword()()) {
            $user = $event->getUser();

            $this->userRepository->update([
                'password_reset_token' => Str::random(32),
            ], $user->getKey());

            $user->refresh();

            $user->notify(new ResetPassword($user->password_reset_token, $event->getReturnUrl()));
        }
    }

    public static function setRunEventForgotPassword(callable $value): void
    {
        self::$runEventForgotPassword = $value;
    }

    public static function getRunEventForgotPassword(): ?callable
    {
        return self::$runEventForgotPassword;
    }
}
