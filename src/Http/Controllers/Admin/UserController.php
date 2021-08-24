<?php

namespace EscolaLms\Auth\Http\Controllers\Admin;

use EscolaLms\Auth\Dtos\Admin\UserUpdateDto;
use EscolaLms\Auth\Dtos\Admin\UserUpdateKeysDto;
use EscolaLms\Auth\Dtos\UserFilterCriteriaDto;
use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Exceptions\UserNotFoundException;
use EscolaLms\Auth\Http\Controllers\Admin\Swagger\UserSwagger;
use EscolaLms\Auth\Http\Requests\Admin\UserAvatarDeleteRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserAvatarUploadRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserCreateRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserDeleteRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserGetRequest;
use EscolaLms\Auth\Http\Requests\Admin\UsersListRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserUpdateRequest;
use EscolaLms\Auth\Http\Resources\UserResource;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;

class UserController extends AbstractUserController implements UserSwagger
{
    public function listUsers(UsersListRequest $request): JsonResponse
    {
        $userFilterDto = UserFilterCriteriaDto::instantiateFromRequest($request);
        $paginator = $this->userService->searchAndPaginate($userFilterDto, $request->except('page'), $request->get('per_page'), $request->get('page'));
        return $this->sendResponseForResource($request, UserResource::collection($paginator));
    }

    public function getUser(UserGetRequest $request): JsonResponse
    {
        try {
            return $this->sendResponseForResource($request, UserResource::make($this->fetchRequestedUser($request)));
        } catch (Exception $ex) {
            return $this->sendError($ex->getMessage(), $ex instanceof UserNotFoundException ? $ex->getCode() : 400);
        }
    }

    public function createUser(UserCreateRequest $request): JsonResponse
    {
        $userSaveDto = UserSaveDto::instantiateFromRequest($request);
        try {
            $user = $this->userService->create($userSaveDto);
            event(new Registered($user));
            return $this->sendResponseForResource($request, UserResource::make($user));
        } catch (Exception $ex) {
            return $this->sendError($ex->getMessage(), 400);
        }
    }

    public function partialUpdateUser(UserUpdateRequest $request): JsonResponse
    {
        $userUpdateDto = UserUpdateDto::instantiateFromRequest($request);
        $userUpdateKeysDto = UserUpdateKeysDto::instantiateFromRequest($request);
        try {
            return $this->sendResponseForResource($request, UserResource::make($this->userService->patchUsingDto($userUpdateDto, $userUpdateKeysDto, $request->route('id'))));
        } catch (Exception $ex) {
            return $this->sendError($ex->getMessage(), 400);
        }
    }

    public function updateUser(UserUpdateRequest $request): JsonResponse
    {
        $userUpdateDto = UserUpdateDto::instantiateFromRequest($request);
        try {
            return $this->sendResponseForResource($request, UserResource::make($this->userService->putUsingDto($userUpdateDto, $request->route('id'))));
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage(), 400);
        }
    }

    public function deleteUser(UserDeleteRequest $request): JsonResponse
    {
        try {
            $deleted = $this->userRepository->delete($request->route('id'));
            if ($deleted) {
                return $this->sendSuccess("User deleted");
            } else {
                return $this->sendError("User not deleted", 422);
            }
        } catch (Exception $ex) {
            return $this->sendError($ex->getMessage(), 400);
        }
    }

    public function uploadAvatar(UserAvatarUploadRequest $request): JsonResponse
    {
        $user = $this->userService->uploadAvatar(
            $this->fetchRequestedUser($request),
            $request->file('avatar'),
        );
        if (!empty($user->path_avatar)) {
            return $this->sendResponse(UserResource::make($user)->toArray($request), __('Avatar uploaded'));
        } else {
            return $this->sendError(__('Avatar not uploaded'), 422);
        }
    }

    public function deleteAvatar(UserAvatarDeleteRequest $request): JsonResponse
    {
        $success = $this->userService->deleteAvatar($this->fetchRequestedUser($request));
        if ($success) {
            return $this->sendSuccess(__('Avatar deleted'));
        } else {
            return $this->sendError(__('Avatar not deleted'), 422);
        }
    }
}
