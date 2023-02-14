<?php

namespace EscolaLms\Auth;

use EscolaLms\Auth\Console\Commands\CreateAdminCommand;
use EscolaLms\Auth\Providers\AuthServiceProvider;
use EscolaLms\Auth\Providers\EventServiceProvider;
use EscolaLms\Auth\Providers\SettingsServiceProvider;
use EscolaLms\Auth\Repositories\Contracts\PreUserRepositoryContract;
use EscolaLms\Auth\Repositories\Contracts\SocialAccountRepositoryContract;
use EscolaLms\Auth\Repositories\Contracts\UserGroupRepositoryContract;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Repositories\PreUserRepository;
use EscolaLms\Auth\Repositories\SocialAccountRepository;
use EscolaLms\Auth\Repositories\UserGroupRepository;
use EscolaLms\Auth\Repositories\UserRepository;
use EscolaLms\Auth\Services\AuthService;
use EscolaLms\Auth\Services\Contracts\AuthServiceContract;
use EscolaLms\Auth\Services\Contracts\SocialAccountServiceContract;
use EscolaLms\Auth\Services\Contracts\UserGroupServiceContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Auth\Services\SocialAccountService;
use EscolaLms\Auth\Services\UserGroupService;
use EscolaLms\Auth\Services\UserService;
use EscolaLms\ModelFields\ModelFieldsServiceProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;

/**
 * SWAGGER_VERSION
 */
class EscolaLmsAuthServiceProvider extends ServiceProvider
{
    const CONFIG_KEY = 'escola_auth';

    public const SERVICES = [
        AuthServiceContract::class => AuthService::class,
        UserGroupServiceContract::class => UserGroupService::class,
        UserServiceContract::class => UserService::class,
        SocialAccountServiceContract::class => SocialAccountService::class,
    ];

    public const REPOSITORIES = [
        UserGroupRepositoryContract::class => UserGroupRepository::class,
        UserRepositoryContract::class => UserRepository::class,
        PreUserRepositoryContract::class => PreUserRepository::class,
        SocialAccountRepositoryContract::class => SocialAccountRepository::class,
    ];

    public array $bindings = self::SERVICES + self::REPOSITORIES;

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', self::CONFIG_KEY);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'auth');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/auth'),
        ]);
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'user');

        $this->app->register(EventServiceProvider::class);
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(SettingsServiceProvider::class);
        $this->app->register(ModelFieldsServiceProvider::class);
        $this->app->register(SocialiteServiceProvider::class);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path(self::CONFIG_KEY . '.php'),
        ], self::CONFIG_KEY . '.config');
        $this->commands([
            CreateAdminCommand::class
        ]);
    }
}
