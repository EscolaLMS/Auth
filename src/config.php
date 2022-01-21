<?php

use EscolaLms\Auth\Enums\SettingStatusEnum;

return [
    'superadmins' => [
        env('AUTH_SUPERADMIN_EMAIL'),
    ],
    'additional_fields' => [],
    'additional_fields_required' => [],
    'registration' => SettingStatusEnum::ENABLED,
    'account_must_be_enabled_by_admin' => SettingStatusEnum::DISABLED,
];
