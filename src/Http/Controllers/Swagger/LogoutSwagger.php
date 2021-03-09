<?php

namespace EscolaLms\Auth\Http\Controllers\Swagger;

use EscolaLms\Auth\Http\Requests\LogoutRequest;
use Illuminate\Http\JsonResponse;

interface LogoutSwagger
{
    /**
     * @OA\Post(
     *      path="/api/auth/logout",
     *      description="User logout",
     *      tags={"Auth"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *      )
     *   )
     */
    public function logout(LogoutRequest $request): JsonResponse;
}
