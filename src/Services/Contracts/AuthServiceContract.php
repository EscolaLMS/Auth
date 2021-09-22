<?php

namespace EscolaLms\Auth\Services\Contracts;

interface AuthServiceContract
{
    public function forgotPassword(string $email, string $returnUrl): void;

    public function resetPassword(string $email, string $token, string $password): void;

    public function getTokenBySocial(string $provider): string;
}
