<?php

namespace EscolaLms\Auth\Http\Controllers\Admin;

use EscolaLms\Auth\Dtos\UserUpdateSettingsDto;
use EscolaLms\Auth\Http\Controllers\Admin\Swagger\UserSettingsSwagger;
use EscolaLms\Auth\Http\Requests\Admin\UserSettingsListRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserSettingsUpdateRequest;
use EscolaLms\Auth\Http\Resources\UserSettingCollection;
use EscolaLms\Auth\Models\User;
use Illuminate\Http\JsonResponse;

class UserSettingsController extends AbstractUserController implements UserSettingsSwagger
{
    public function listUserSettings(UserSettingsListRequest $request): JsonResponse
    {
        $user = $this->fetchRequestedUser($request);
        return $this->generateUserSettingsCollectionResponse($user);
    }

    private function generateUserSettingsCollectionResponse(User $user): JsonResponse
    {
        $user = $user->refresh();
        $collectionJsonResource = new UserSettingCollection($user->settings);
        return $collectionJsonResource->response();
    }

    public function patchUserSettings(UserSettingsUpdateRequest $request): JsonResponse
    {
        $user = $this->fetchRequestedUser($request);
        $dto = UserUpdateSettingsDto::instantiateFromRequest($request);
        try {
            $this->userRepository->patchSettingsUsingDto($user, $dto);
            return $this->generateUserSettingsCollectionResponse($user);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        }
    }

    public function putUserSettings(UserSettingsUpdateRequest $request): JsonResponse
    {
        $user = $this->fetchRequestedUser($request);
        $dto = UserUpdateSettingsDto::instantiateFromRequest($request);
        try {
            $this->userRepository->putSettingsUsingDto($user, $dto);
            return $this->generateUserSettingsCollectionResponse($user);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        }
    }
}
