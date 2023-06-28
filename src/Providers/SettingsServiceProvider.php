<?php

namespace EscolaLms\Auth\Providers;

use EscolaLms\Auth\Enums\SettingStatusEnum;
use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Settings\EscolaLmsSettingsServiceProvider;
use EscolaLms\Settings\Facades\AdministrableConfig;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{

    public function register()
    {
        if (class_exists(\EscolaLms\Settings\EscolaLmsSettingsServiceProvider::class)) {
            if (!$this->app->getProviders(EscolaLmsSettingsServiceProvider::class)) {
                $this->app->register(EscolaLmsSettingsServiceProvider::class);
            }
            AdministrableConfig::registerConfig(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.registration', ['required', 'string', 'in:' . implode(',', SettingStatusEnum::getValues())]);
            AdministrableConfig::registerConfig(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.account_must_be_enabled_by_admin', ['required', 'string', 'in:' . implode(',', SettingStatusEnum::getValues())]);
            AdministrableConfig::registerConfig(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.auto_verified_email', ['required', 'string', 'in:' . implode(',', SettingStatusEnum::getValues())]);
            AdministrableConfig::registerConfig(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.return_url', ['required', 'url']);

            // SOCIALITE
            AdministrableConfig::registerConfig('services.facebook.client_id', ['required', 'string'], false);
            AdministrableConfig::registerConfig('services.facebook.client_secret',  ['required', 'string'], false);
            AdministrableConfig::registerConfig('services.facebook.redirect', ['required', 'url'], false);
            AdministrableConfig::registerConfig('services.google.client_id', ['required', 'string'], false);
            AdministrableConfig::registerConfig('services.google.client_secret', ['required', 'string'], false);
            AdministrableConfig::registerConfig('services.google.redirect', ['required', 'url'], false);
        }
    }
}
