<?php

namespace EscolaLms\Auth\Exceptions;

use Exception;

class OnboardingNotCompleted extends Exception
{
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? 'Onboarding not completed');
    }
}
