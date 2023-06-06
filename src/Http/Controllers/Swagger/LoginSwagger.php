<?php

namespace EscolaLms\Auth\Http\Controllers\Swagger;

use EscolaLms\Auth\Http\Requests\ImpersonateRequest;
use EscolaLms\Auth\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;

interface LoginSwagger
{
    /**
     * @OA\Post(
     *      path="/api/auth/login",
     *      description="User login",
     *      tags={"Auth"},
     *      @OA\Parameter(
     *          name="email",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="remember_me",
     *          in="query",
     *          @OA\Schema(
     *              enum={0,1},
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function login(LoginRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/auth/impersonate",
     *      description="User impersonate",
     *      tags={"Auth"},
     *      @OA\Parameter(
     *          name="user_id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function impersonate(ImpersonateRequest $request): JsonResponse;
}
