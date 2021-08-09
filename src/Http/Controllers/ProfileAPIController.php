<?php

namespace EscolaLms\Auth\Http\Controllers;

use EscolaLms\Auth\Dtos\UserUpdateAuthDataDto;
use EscolaLms\Auth\Dtos\UserUpdateDto;
use EscolaLms\Auth\Http\Requests\ProfileUpdateAuthDataRequest;
use EscolaLms\Auth\Http\Requests\ProfileUpdatePasswordRequest;
use EscolaLms\Auth\Http\Requests\ProfileUpdateRequest;
use EscolaLms\Auth\Http\Requests\UploadAvatarRequest;
use EscolaLms\Auth\Http\Requests\MyProfileRequest;
use EscolaLms\Auth\Http\Requests\UpdateInterests;
use EscolaLms\Auth\Http\Requests\UserSettingsUpdateRequest;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Auth\Http\Controllers\Swagger\ProfileSwagger;
use EscolaLms\Auth\Http\Resources\UserResource;
use EscolaLms\Auth\Http\Resources\UserSettingCollection;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
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
        return $this->sendResponse(UserResource::make($request->user())->toArray($request), 'My profile');
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $userUpdateDto = UserUpdateDto::instantiateFromRequest($request);

        $user = $this->userRepository->update(
            $userUpdateDto->toArray(),
            $request->user()->getKey(),
        );

        return $this->sendResponse(UserResource::make($user)->toArray($request), __('Updated profile'));
    }

    public function updateAuthData(ProfileUpdateAuthDataRequest $request): JsonResponse
    {
        $userUpdateDto = UserUpdateAuthDataDto::instantiateFromRequest($request);

        $user = $this->userRepository->update(
            $userUpdateDto->toArray(),
            $request->user()->getKey(),
        );

        return $this->sendSuccess(UserResource::make($user)->toArray($user), __('Updated email'));
    }

    public function updatePassword(ProfileUpdatePasswordRequest $request): JsonResponse
    {
        $success = $this->userRepository->updatePassword(
            $request->user(),
            $request->input('new_password'),
        );

        return new JsonResponse(['success' => $success], $success ? 200 : 422);
        if ($success) {
            return $this->sendSuccess(__('Password updated'));
        } else {
            $this->sendError(__('Password not updated', 422));
        }
    }

    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $avatarUrl = $this->userService->uploadAvatar(
            $request->user(),
            $request->file('avatar'),
        );
        if (!empty($avatarUrl)) {
            return $this->sendResponse(['avatar_url' => $avatarUrl], __('Avatar uploaded'));
        } else {
            return $this->sendError(__('Avatar not uploaded'), 422);
        }
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $success = $this->userService->deleteAvatar($request->user());
        if ($success) {
            return $this->sendSuccess(__('Avatar deleted'));
        } else {
            return $this->sendError(__('Avatar not deleted'), 422);
        }
    }

    public function interests(UpdateInterests $request): JsonResponse
    {
        $this->userRepository->updateInterests(
            $request->user(),
            $request->input('interests'),
        );

        return $this->sendResponse(UserResource::make($request->user())->toArray($request), '');
    }

    public function settings(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->sendResponse(UserSettingCollection::make($user->settings)->toArray($request), '');
    }

    public function settingsUpdate(UserSettingsUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->userRepository->updateSettings($user, $request->all());

        return $this->sendResponse(UserSettingCollection::make($user->settings)->toArray($request), '');
    }
}
