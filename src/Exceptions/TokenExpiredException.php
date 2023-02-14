<?php

namespace EscolaLms\Auth\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TokenExpiredException extends AuthException
{
    public function __construct(?string $message = null, int $code = Response::HTTP_BAD_REQUEST, ?Throwable $previous = null) {
        parent::__construct($message ?? __('Token has expired'), $code, $previous);
    }
}
