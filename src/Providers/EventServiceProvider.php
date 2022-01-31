<?php

namespace EscolaLms\Auth\Providers;

use EscolaLms\Auth\Events\AccountRegistered;
use EscolaLms\Auth\Events\ForgotPassword;
use EscolaLms\Auth\Listeners\CreatePasswordResetToken;
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
