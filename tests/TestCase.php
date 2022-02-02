<?php

namespace EscolaLms\Auth\Tests;

use EscolaLms\Auth\Database\Seeders\AuthPermissionSeeder;
use EscolaLms\Core\Tests\TestCase as CoreTestCase;
use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Tests\Models\Client;
use EscolaLms\Categories\EscolaLmsCategoriesServiceProvider;
use EscolaLms\Templates\EscolaLmsTemplatesServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends CoreTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Passport::useClientModel(Client::class);
        $this->seed(AuthPermissionSeeder::class);
    }

    protected function getPackageProviders($app)
    {
        $providers = [
            ...parent::getPackageProviders($app),
            EscolaLmsAuthServiceProvider::class,
            PermissionServiceProvider::class,
            PassportServiceProvider::class,
            EscolaLmsCategoriesServiceProvider::class,
        ];

        if (class_exists(EscolaLmsTemplatesServiceProvider::class)) {
            array_push($providers, EscolaLmsTemplatesServiceProvider::class);
        }

        return $providers;
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('passport.client_uuids', true);
    }
}
