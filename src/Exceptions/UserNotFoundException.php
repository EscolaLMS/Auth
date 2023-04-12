<?php

namespace EscolaLms\Auth\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class UserNotFoundException extends ModelNotFoundException
{
    public function __construct($message = "User Not Found", $code = 422, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
