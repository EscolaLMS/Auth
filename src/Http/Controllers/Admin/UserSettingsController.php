<?php

namespace EscolaLms\Auth\Http\Controllers\Admin;

use EscolaLms\Auth\Dtos\UserUpdateSettingsDto;
use EscolaLms\Auth\Http\Controllers\Admin\Swagger\UserSettingsSwagger;
use EscolaLms\Auth\Http\Requests\Admin\UserSettingsListRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserSettingsUpdateRequest;
use EscolaLms\Auth\Http\Resources\UserSettingCollection;
use EscolaLms\Auth\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserSettingsController extends AbstractUserController implements UserSettingsSwagger
{
    public function listUserSettings(UserSettingsListRequest $request): JsonResponse
    {
        $user = $this->fetchRequestedUser($request);
        return $this->generateUserSettingsCollectionResponse($request, $user);
    }

    public function patchUserSettings(UserSettingsUpdateRequest $request): JsonResponse
    {
        $user = $this->fetchRequestedUser($request);
        $dto = UserUpdateSettingsDto::instantiateFromRequest($request);
        try {
            $this->userRepository->patchSettingsUsingDto($user, $dto);
            return $this->generateUserSettingsCollectionResponse($request, $user);
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
            return $this->generateUserSettingsCollectionResponse($request, $user);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        }
    }

    private function generateUserSettingsCollectionResponse(Request $request, User $user): JsonResponse
    {
        $user = $user->refresh();
        return $this->sendResponseForResource($request, UserSettingCollection::make($user->settings));
    }
}
