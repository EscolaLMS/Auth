<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Dtos\UserUpdateSettingsDto;
use EscolaLms\Auth\Enums\AuthPermissionsEnum;
use EscolaLms\Auth\Enums\SettingStatusEnum;
use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Events\AccountConfirmed;
use EscolaLms\Auth\Events\AccountMustBeEnableByAdmin;
use EscolaLms\Auth\Events\AccountRegistered;
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
        $mustBeEnabledByAdmin = Config::get(
            EscolaLmsAuthServiceProvider::CONFIG_KEY . '.account_must_be_enabled_by_admin', SettingStatusEnum::DISABLED
        );

        $autoVerifiedEmail = Config::get(
            EscolaLmsAuthServiceProvider::CONFIG_KEY . '.auto_verified_email', SettingStatusEnum::DISABLED
        );

        $userSaveDto = UserSaveDto::instantiateFromRequest($request)->setRoles([UserRole::STUDENT]);
        $userSaveDto->setIsActive($mustBeEnabledByAdmin === SettingStatusEnum::DISABLED);
        $userSettingsDto = UserUpdateSettingsDto::instantiateFromRequest($request);
        $user = $this->userService->createWithSettings($userSaveDto, $userSettingsDto);
        $this->userService->updateAdditionalFieldsFromRequest($user, $request);
        $this->userGroupService->registerMemberToMultipleGroups($request->input('groups', []), $user);

        if ($mustBeEnabledByAdmin === SettingStatusEnum::ENABLED) {
            User::permission(AuthPermissionsEnum::USER_VERIFY_ACCOUNT)->get()->each(function ($admin) use ($user) {
                event(new AccountMustBeEnableByAdmin($admin, $user));
            });

            return $this->sendSuccess(__('Registered, account must be enabled by admin'));
        } else {
            if ($autoVerifiedEmail === SettingStatusEnum::ENABLED) {
                $user->markEmailAsVerified();
                event(new AccountConfirmed($user));
            } else {
                event(new AccountRegistered($user, $request->input('return_url')));
            }
        }

        return $this->sendSuccess(__('Registered'));
    }
}
