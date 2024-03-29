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
            AdministrableConfig::registerConfig(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.socialite_remember_me', ['required', 'boolean']);

            // SOCIALITE
            AdministrableConfig::registerConfig('services.facebook.client_id', ['nullable', 'string'], false);
            AdministrableConfig::registerConfig('services.facebook.client_secret',  ['nullable', 'string'], false);
            AdministrableConfig::registerConfig('services.facebook.redirect', ['nullable', 'url'], false);
            AdministrableConfig::registerConfig('services.google.client_id', ['nullable', 'string'], false);
            AdministrableConfig::registerConfig('services.google.client_secret', ['nullable', 'string'], false);
            AdministrableConfig::registerConfig('services.google.redirect', ['nullable', 'url'], false);
        }
    }
}
