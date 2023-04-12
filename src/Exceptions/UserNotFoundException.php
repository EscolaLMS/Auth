<?php

namespace EscolaLms\Auth\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

class UserNotFoundException extends UnprocessableEntityHttpException
{
    public function __construct($message = "User Not Found", $code = 422, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous, $code);
    }
}
