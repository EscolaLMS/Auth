<?php

namespace EscolaLms\Auth;

use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Repositories\UserRepository;
use EscolaLms\Auth\Services\AuthService;
use EscolaLms\Auth\Services\Contracts\AuthServiceContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
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
        UserServiceContract::class => UserService::class,
        UserRepositoryContract::class => UserRepository::class
    ];

    public function register()
    {
        $this->injectContract(self::CONTRACTS);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->app->register(EventServiceProvider::class);
    }
}
