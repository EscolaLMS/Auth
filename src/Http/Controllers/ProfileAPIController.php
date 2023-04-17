<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\Dtos\UserUpdateAuthDataDto;
use EscolaLms\Auth\Dtos\UserUpdateDto;
use EscolaLms\Auth\Http\Controllers\Swagger\ProfileSwagger;
use EscolaLms\Auth\Http\Requests\MyProfileRequest;
use EscolaLms\Auth\Http\Requests\ProfileUpdateAuthDataRequest;
use EscolaLms\Auth\Http\Requests\ProfileUpdatePasswordRequest;
use EscolaLms\Auth\Http\Requests\ProfileUpdateRequest;
use EscolaLms\Auth\Http\Requests\UpdateInterests;
use EscolaLms\Auth\Http\Requests\UploadAvatarRequest;
use EscolaLms\Auth\Http\Requests\UserSettingsUpdateRequest;
use EscolaLms\Auth\Http\Resources\UserFullResource;
use EscolaLms\Auth\Http\Resources\UserSettingCollection;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Core\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileAPIController extends EscolaLmsBaseController implements ProfileSwagger
{
    private UserRepositoryContract $userRepository;
    private UserServiceContract $userService;

    public function __construct(UserRepositoryContract $userRepository, UserServiceContract $userService)
    {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    public function me(MyProfileRequest $request): JsonResponse
    {
        return $this->sendResponseForResource(UserFullResource::make($request->user()), 'My profile');
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $userUpdateDto = UserUpdateDto::instantiateFromRequest($request);
        var_dump($userUpdateDto->toArray(true));
        /** @var User $user */
        $user = $this->userRepository->update(
            $userUpdateDto->toArray(true),
            $request->user()->getKey(),
        );
        $this->userService->updateAdditionalFieldsFromRequest($user, $request);

        if (!is_null($user)) {
            return $this->sendResponseForResource(UserFullResource::make($user->refresh()), __('Updated profile'));
        }

        return $this->sendError(__('Profile not updated'), 422);
    }

    public function updateAuthData(ProfileUpdateAuthDataRequest $request): JsonResponse
    {
        $userUpdateDto = UserUpdateAuthDataDto::instantiateFromRequest($request);

        $user = $this->userRepository->update(
            $userUpdateDto->toArray(),
            $request->user()->getKey(),
        );

        if (!is_null($user)) {
            return $this->sendResponseForResource(UserFullResource::make($user), __('Updated email'));
        }

        return $this->sendError(__('Email not updated'), 422);
    }

    public function updatePassword(ProfileUpdatePasswordRequest $request): JsonResponse
    {
        $success = $this->userRepository->updatePassword(
            $request->user(),
            $request->input('new_password'),
        );

        if ($success) {
            return $this->sendSuccess(__('Password updated'));
        }
        return $this->sendError(__('Password not updated', 422));
    }

    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $this->userService->uploadAvatar(
            $request->user(),
            $request->file('avatar'),
        );

        if (!is_null($user)) {
            return $this->sendResponseForResource(UserFullResource::make($user), __('Avatar uploaded'));
        }

        return $this->sendError(__('Avatar not uploaded'), 422);
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $success = $this->userService->deleteAvatar($request->user());

        if ($success) {
            return $this->sendSuccess(__('Avatar deleted'));
        }

        return $this->sendError(__('Avatar not deleted'), 422);
    }

    public function interests(UpdateInterests $request): JsonResponse
    {
        $this->userRepository->updateInterests(
            $request->user(),
            $request->input('interests'),
        );

        return $this->sendResponseForResource(UserFullResource::make($request->user()->refresh()), __('Updated user interests'));
    }

    public function settings(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->sendResponseForResource(UserSettingCollection::make($user->settings), __('User settings'));
    }

    public function settingsUpdate(UserSettingsUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->userRepository->updateSettings($user, $request->getSettingsWithoutAdditionalFields());
        return $this->sendResponseForResource(UserSettingCollection::make($user->settings), __('User interests'));
    }
}
