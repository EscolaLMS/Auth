<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Dtos\UserUpdateSettingsDto;
use EscolaLms\Auth\Enums\AuthPermissionsEnum;
use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Events\EscolaLmsAccountMustBeEnableByAdminTemplateEvent;
use EscolaLms\Auth\Events\EscolaLmsAccountRegisteredTemplateEvent;
use EscolaLms\Auth\Http\Controllers\Swagger\RegisterSwagger;
use EscolaLms\Auth\Http\Requests\RegisterRequest;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Services\Contracts\UserGroupServiceContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;

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
        $mustBeEnabledByAdmin = Config::get(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.account_must_be_enabled_by_admin', false);
        $userSaveDto = UserSaveDto::instantiateFromRequest($request)->setRoles([UserRole::STUDENT]);
        $userSaveDto->setIsActive(!$mustBeEnabledByAdmin);
        $userSettingsDto = UserUpdateSettingsDto::instantiateFromRequest($request);
        $user = $this->userService->createWithSettings($userSaveDto, $userSettingsDto);
        $this->userService->updateAdditionalFieldsFromRequest($user, $request);
        $this->userGroupService->registerMemberToMultipleGroups($request->input('groups', []), $user);

        if ($mustBeEnabledByAdmin) {
            User::permission(AuthPermissionsEnum::USER_VERIFY_ACCOUNT)->get()->each(function ($admin) use ($user) {
                event(new EscolaLmsAccountMustBeEnableByAdminTemplateEvent($admin, $user));
            });

            return $this->sendSuccess(__('Registered, account must be enabled by admin'));
        } else {
            event(new EscolaLmsAccountRegisteredTemplateEvent($user));
        }

        return $this->sendSuccess(__('Registered'));
    }
}
