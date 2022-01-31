<?php

namespace EscolaLms\Auth\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class SendEmailVerificationNotification
{
    private static $runEventEmailVerification;

    public function handle(Registered $event)
    {
        if (!is_callable(self::getRunEventEmailVerification()) || self::getRunEventEmailVerification()()) {
            if ($event->user instanceof MustVerifyEmail && !$event->user->hasVerifiedEmail()) {
                $event->user->sendEmailVerificationNotification();
            }
        }
    }

    public static function setRunEventEmailVerification(callable $value): void
    {
        self::$runEventEmailVerification = $value;
    }

    public static function getRunEventEmailVerification(): ?callable
    {
        return self::$runEventEmailVerification;
    }
}
