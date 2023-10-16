<?php

use EscolaLms\Auth\Enums\SettingStatusEnum;

return [
    'superadmins' => [
        env('AUTH_SUPERADMIN_EMAIL'),
    ],
    'registration' => SettingStatusEnum::ENABLED,
    'account_must_be_enabled_by_admin' => SettingStatusEnum::DISABLED,
    'auto_verified_email' => SettingStatusEnum::DISABLED,
    'return_url' => null,
    'socialite_remember_me' => false,
];
