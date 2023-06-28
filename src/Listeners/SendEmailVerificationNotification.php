<?php

namespace EscolaLms\Auth\Listeners;

use EscolaLms\Auth\Enums\SettingStatusEnum;
use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Config;

class SendEmailVerificationNotification
{
    private static $runEventEmailVerification;

    public function handle(Registered $event)
    {
        $autoVerifiedEmail = Config::get(
            EscolaLmsAuthServiceProvider::CONFIG_KEY . '.auto_verified_email', SettingStatusEnum::DISABLED
        );

        if ($autoVerifiedEmail === SettingStatusEnum::ENABLED) {
            return;
        }

        if (!is_callable(self::getRunEventEmailVerification()) || self::getRunEventEmailVerification()()) {
            if ( $event->user instanceof MustVerifyEmail && !$event->user->hasVerifiedEmail()) {
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
