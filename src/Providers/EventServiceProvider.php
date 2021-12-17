<?php

namespace EscolaLms\Auth\Providers;

use EscolaLms\Auth\Events\EscolaLmsAccountRegisteredTemplateEvent;
use EscolaLms\Auth\Events\EscolaLmsForgotPasswordTemplateEvent;
use EscolaLms\Auth\Listeners\CreatePasswordResetToken;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        EscolaLmsAccountRegisteredTemplateEvent::class => [
            SendEmailVerificationNotification::class,
        ],
        EscolaLmsForgotPasswordTemplateEvent::class => [
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
