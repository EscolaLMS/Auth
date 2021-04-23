<?php

namespace EscolaLms\Auth\Http\Controllers\Admin;

use EscolaLms\Auth\Dtos\Admin\UserUpdateDto;
use EscolaLms\Auth\Dtos\Admin\UserUpdateKeysDto;
use EscolaLms\Auth\Dtos\UserFilterCriteriaDto;
use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Http\Controllers\Admin\Swagger\UserSwagger;
use EscolaLms\Auth\Http\Requests\Admin\UserAvatarDeleteRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserAvatarUploadRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserCreateRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserDeleteRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserGetRequest;
use EscolaLms\Auth\Http\Requests\Admin\UsersListRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserUpdateRequest;
use EscolaLms\Auth\Http\Resources\UserResource;
use EscolaLms\Core\Dtos\PaginationDto;
use Illuminate\Http\JsonResponse;

class UserController extends AbstractUserController implements UserSwagger
{
    public function listUsers(UsersListRequest $request): JsonResponse
    {
        $userFilterDto = UserFilterCriteriaDto::instantiateFromRequest($request);
        $paginationDto = PaginationDto::instantiateFromRequest($request);
        return UserResource::collection($this->userService->search($userFilterDto, $paginationDto))->response();
    }

    public function getUser(UserGetRequest $request): JsonResponse
    {
        try {
            return (new UserResource($this->userRepository->find($request->route('id'))))->response();
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        }
    }

    public function createUser(UserCreateRequest $request): JsonResponse
    {
        $userSaveDto = UserSaveDto::instantiateFromRequest($request);
        try {
            return (new UserResource($this->userService->create($userSaveDto)))->response();
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        }
    }

    public function partialUpdateUser(UserUpdateRequest $request): JsonResponse
    {
        $userUpdateDto = UserUpdateDto::instantiateFromRequest($request);
        $userUpdateKeysDto = UserUpdateKeysDto::instantiateFromRequest($request);
        try {
            return (new UserResource($this->userService->patchUsingDto($userUpdateDto, $userUpdateKeysDto, $request->route('id'))))->response();
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        }
    }

    public function updateUser(UserUpdateRequest $request): JsonResponse
    {
        $userUpdateDto = UserUpdateDto::instantiateFromRequest($request);
        try {
            return (new UserResource($this->userService->putUsingDto($userUpdateDto, $request->route('id'))))->response();
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        }
    }

    public function deleteUser(UserDeleteRequest $request): JsonResponse
    {
        try {
            $deleted = $this->userRepository->delete($request->route('id'));
            if ($deleted) {
                return new JsonResponse("User deleted", 200);
            } else {
            }
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        }
    }

    public function uploadAvatar(UserAvatarUploadRequest $request): JsonResponse
    {
        $avatarUrl = $this->userService->uploadAvatar(
            $this->fetchRequestedUser($request),
            $request->file('avatar'),
        );
        if (!empty($avatarUrl)) {
            return new JsonResponse(['success' => true, 'avatar_url' => $avatarUrl], 200);
        } else {
            return new JsonResponse(['error' => ''], 422);
        }
    }

    public function deleteAvatar(UserAvatarDeleteRequest $request): JsonResponse
    {
        $success = $this->userService->deleteAvatar($this->fetchRequestedUser($request));
        return new JsonResponse(['success' => $success], $success ? 200 : 422);
    }
}
