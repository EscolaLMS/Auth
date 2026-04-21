<?php

use EscolaLms\Auth\Enums\SettingStatusEnum;
use EscolaLms\Auth\Enums\TokenExpirationEnum;

return [
    'superadmins' => [
        env('AUTH_SUPERADMIN_EMAIL'),
    ],
    'registration' => SettingStatusEnum::ENABLED,
    'account_must_be_enabled_by_admin' => SettingStatusEnum::DISABLED,
    'auto_verified_email' => SettingStatusEnum::DISABLED,
    'return_url' => null,
    'socialite_remember_me' => false,
    'token_expiration_minutes' => TokenExpirationEnum::SHORT_TIME_IN_MINUTES,
];
