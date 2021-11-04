<?php

namespace EscolaLms\Auth;

use EscolaLms\Auth\Repositories\Contracts\UserGroupRepositoryContract;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Repositories\UserGroupRepository;
use EscolaLms\Auth\Repositories\UserRepository;
use EscolaLms\Auth\Services\AuthService;
use EscolaLms\Auth\Services\Contracts\AuthServiceContract;
use EscolaLms\Auth\Services\Contracts\UserGroupServiceContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Auth\Services\UserGroupService;
use EscolaLms\Auth\Services\UserService;
use EscolaLms\Core\Providers\Injectable;
use Illuminate\Support\ServiceProvider;

/**
 * SWAGGER_VERSION
 */
class EscolaLmsAuthServiceProvider extends ServiceProvider
{
    use Injectable;

    private const CONTRACTS = [
        AuthServiceContract::class => AuthService::class,
        UserGroupRepositoryContract::class => UserGroupRepository::class,
        UserGroupServiceContract::class => UserGroupService::class,
        UserRepositoryContract::class => UserRepository::class,
        UserServiceContract::class => UserService::class,
    ];

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'escola_auth');

        $this->injectContract(self::CONTRACTS);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(AuthServiceProvider::class);
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
            __DIR__ . '/config.php' => config_path('escola_auth.php'),
        ], 'escola_auth.config');
    }
}
