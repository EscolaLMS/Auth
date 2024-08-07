<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\Http\Controllers\Swagger\LoginSwagger;
use EscolaLms\Auth\Http\Requests\ImpersonateRequest;
use EscolaLms\Auth\Http\Requests\LoginRequest;
use EscolaLms\Auth\Http\Resources\LoginResource;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Services\Contracts\AuthServiceContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Exception;
use Illuminate\Http\JsonResponse;
use Sentry\Tracing\TransactionContext;

class LoginApiController extends EscolaLmsBaseController implements LoginSwagger
{
    private UserServiceContract $userService;
    private AuthServiceContract $authService;

    public function __construct(UserServiceContract $userService, AuthServiceContract $authServiceContract)
    {
        $this->userService = $userService;
        $this->authService = $authServiceContract;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->userService->login(
                $request->input('email'),
                $request->input('password'),
            );

            $token = $this->authService->createTokenForUser($user, $request->boolean('remember_me'));

            return $this->sendResponseForResource(LoginResource::make($token), __('Login successful'));
        } catch (Exception $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 422);
            return $this->sendError($exception->getMessage(), 422);
        }
    }

    public function impersonate(ImpersonateRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->userService->impersonate(
                $request->get('user_id')
            );

            $token = $this->authService->createTokenForUser($user);

            return $this->sendResponseForResource(LoginResource::make($token), __('Impersonate successful'));
        } catch (Exception $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], 422);
            return $this->sendError($exception->getMessage(), 422);
        }
    }
}
