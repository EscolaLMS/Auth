<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Http\Controllers\Swagger\RegisterSwagger;
use EscolaLms\Auth\Http\Requests\RegisterRequest;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;

class RegisterApiController extends EscolaLmsBaseController implements RegisterSwagger
{
    private UserServiceContract $userService;

    public function __construct(UserServiceContract $userService)
    {
        $this->userService = $userService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $userSaveDto = UserSaveDto::instantiateFromRequest($request);
        $user = $this->userService->create($userSaveDto);
        event(new Registered($user));

        return $this->sendSuccess(__('Registered'));
    }
}
