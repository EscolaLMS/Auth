<?php

namespace EscolaLms\Auth\Providers;

use EscolaLms\Auth\Events\AccountDeleted;
use EscolaLms\Auth\Events\AccountRegistered;
use EscolaLms\Auth\Events\ForgotPassword;
use EscolaLms\Auth\Listeners\CreatePasswordResetToken;
use EscolaLms\Auth\Listeners\EmailAnonymisation;
use EscolaLms\Auth\Listeners\RemoveUserSocialAccounts;
use EscolaLms\Auth\Listeners\SendEmailVerificationNotification;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        AccountRegistered::class => [
            SendEmailVerificationNotification::class,
        ],
        ForgotPassword::class => [
            CreatePasswordResetToken::class,
        ],
        AccountDeleted::class => [
            EmailAnonymisation::class,
            RemoveUserSocialAccounts::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
