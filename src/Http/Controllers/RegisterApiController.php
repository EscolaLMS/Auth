<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Dtos\UserUpdateSettingsDto;
use EscolaLms\Auth\Events\EscolaLmsAccountRegisteredTemplateEvent;
use EscolaLms\Auth\Http\Controllers\Swagger\RegisterSwagger;
use EscolaLms\Auth\Http\Requests\RegisterRequest;
use EscolaLms\Auth\Services\Contracts\UserGroupServiceContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;

class RegisterApiController extends EscolaLmsBaseController implements RegisterSwagger
{
    private UserServiceContract $userService;
    private UserGroupServiceContract $userGroupService;

    public function __construct(UserServiceContract $userService, UserGroupServiceContract $userGroupService)
    {
        $this->userService = $userService;
        $this->userGroupService = $userGroupService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $userSaveDto = UserSaveDto::instantiateFromRequest($request)->setRoles([UserRole::STUDENT]);
        $userSettingsDto = UserUpdateSettingsDto::instantiateFromRequest($request);
        $user = $this->userService->createWithSettings($userSaveDto, $userSettingsDto);
        $this->userGroupService->registerMemberToMultipleGroups($request->input('groups', []), $user);

        event(new EscolaLmsAccountRegisteredTemplateEvent($user));

        return $this->sendSuccess(__('Registered'));
    }
}
