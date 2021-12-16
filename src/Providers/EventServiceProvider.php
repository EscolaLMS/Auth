<?php

namespace EscolaLms\Auth\Providers;

use EscolaLms\Auth\Events\PasswordForgotten;
use EscolaLms\Auth\Listeners\CreatePasswordResetToken;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PasswordForgotten::class => [
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
