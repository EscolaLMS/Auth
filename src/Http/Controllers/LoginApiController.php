<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\Http\Controllers\Swagger\LoginSwagger;
use EscolaLms\Auth\Http\Requests\LoginRequest;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Exception;
use Illuminate\Http\JsonResponse;

class LoginApiController extends EscolaLmsBaseController implements LoginSwagger
{
    private UserServiceContract $userService;

    public function __construct(UserServiceContract $userService)
    {
        $this->userService = $userService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->login(
                $request->input('email'),
                $request->input('password'),
            );

            $token = $user->createToken("EscolaLMS User Token")->accessToken;

            return $this->sendResponse(['token' => $token], __('Login successful'));
        } catch (Exception $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 422);
            return $this->sendError($exception->getMessage(), 422);
        }
    }
}
