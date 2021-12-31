<?php

namespace EscolaLms\Auth\Providers;

use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Rules\AdditionalFieldsRequiredInConfig;
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
            AdministrableConfig::registerConfig(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.additional_fields', ['required', 'array']);
            AdministrableConfig::registerConfig(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.additional_fields_required', ['required', 'array', new AdditionalFieldsRequiredInConfig()]);
            AdministrableConfig::registerConfig(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.registration_enabled', ['required', 'boolean']);
            AdministrableConfig::registerConfig(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.account_must_be_enabled_by_admin', ['required', 'boolean'], false);
        }
    }
}
