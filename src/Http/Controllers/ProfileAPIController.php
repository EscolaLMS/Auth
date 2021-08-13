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
        return (new UserResource($request->user()))->response();
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $userUpdateDto = UserUpdateDto::instantiateFromRequest($request);
        $model = $this->userRepository->update(
            $userUpdateDto->toArray(),
            $request->user()->getKey(),
        );

        $success = isset($model);

        return new JsonResponse(['success' => $success, 'data' => $model], $success ? 200 : 422);
    }

    public function updateAuthData(ProfileUpdateAuthDataRequest $request): JsonResponse
    {
        $userUpdateDto = UserUpdateAuthDataDto::instantiateFromRequest($request);
        $model = $this->userRepository->update(
            $userUpdateDto->toArray(),
            $request->user()->getKey(),
        );

        $success = isset($model);

        return new JsonResponse(['success' => $success, 'data' => $model], $success ? 200 : 422);
    }

    public function updatePassword(ProfileUpdatePasswordRequest $request): JsonResponse
    {
        $success = $this->userRepository->updatePassword(
            $request->user(),
            $request->input('new_password'),
        );

        return new JsonResponse(['success' => $success], $success ? 200 : 422);
    }

    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $this->userService->uploadAvatar(
            $request->user(),
            $request->file('avatar'),
        );
        $success = (bool)$user;
        return new JsonResponse(['success' => $success, 'data' => $user], $success ? 200 : 422);
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $success = $this->userService->deleteAvatar($request->user());
        return new JsonResponse(['success' => $success], $success ? 200 : 422);
    }

    public function interests(UpdateInterests $request): JsonResponse
    {
        $this->userRepository->updateInterests(
            $request->user(),
            $request->input('interests'),
        );

        return (new UserResource($request->user()))->response();
    }

    public function settings(Request $request): JsonResponse
    {
        $user = $request->user();

        return (new UserSettingCollection($user->settings))->response();
    }

    public function settingsUpdate(UserSettingsUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->userRepository->updateSettings($user, $request->all());

        return (new UserSettingCollection($user->settings))->response();
    }
}
