<?php

namespace EscolaLms\Auth\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Illuminate\Http\JsonResponse;

class DeletionTokenExpiredException extends AuthException
{
    public function __construct(?string $message = null, int $code = Response::HTTP_BAD_REQUEST, ?Throwable $previous = null)
    {
        parent::__construct($message ?? __('Deletion token has expired'), $code, $previous);
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], Response::HTTP_UNAUTHORIZED);
    }
}
