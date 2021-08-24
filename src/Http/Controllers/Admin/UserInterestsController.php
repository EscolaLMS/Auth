<?php

namespace EscolaLms\Auth\Http\Controllers\Admin;

use EscolaLms\Auth\Dtos\UserUpdateInterestsDto;
use EscolaLms\Auth\Http\Controllers\Admin\Swagger\UserInterestsSwagger;
use EscolaLms\Auth\Http\Requests\Admin\UserInterestAddRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserInterestDeleteRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserInterestsListRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserInterestsUpdateRequest;
use EscolaLms\Auth\Http\Resources\UserInterestCollection;
use Illuminate\Http\JsonResponse;
use EscolaLms\Auth\Models\User;
use Illuminate\Http\Request;

class UserInterestsController extends AbstractUserController implements UserInterestsSwagger
{
    public function listUserInterests(UserInterestsListRequest $request): JsonResponse
    {
        $user = $this->fetchRequestedUser($request);
        return $this->generateUserInterestsCollectionResponse($request, $user);
    }

    public function updateUserInterests(UserInterestsUpdateRequest $request): JsonResponse
    {
        $user = $this->fetchRequestedUser($request);
        $dto = UserUpdateInterestsDto::instantiateFromRequest($request);
        try {
            $this->userRepository->updateInterestsUsingDto($user, $dto);
            return $this->generateUserInterestsCollectionResponse($request, $user);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        }
    }

    public function addUserInterest(UserInterestAddRequest $request): JsonResponse
    {
        $user = $this->fetchRequestedUser($request);
        $interest_id = $request->validated()['interest_id'];
        try {
            $this->userRepository->addInterestById($user, $interest_id);
            return $this->generateUserInterestsCollectionResponse($request, $user);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        }
    }

    public function deleteUserInterest(UserInterestDeleteRequest $request): JsonResponse
    {
        $user = $this->fetchRequestedUser($request);
        $interest_id = $request->validated()['interest_id'];
        try {
            $this->userRepository->removeInterestById($user, $interest_id);
            return $this->generateUserInterestsCollectionResponse($request, $user);
        } catch (\Exception $ex) {
            return new JsonResponse(['error' => $ex->getMessage()], 400);
        }
    }

    private function generateUserInterestsCollectionResponse(Request $request, User $user): JsonResponse
    {
        $user = $user->refresh();
        return $this->sendResponseForResource(UserInterestCollection::make($user->interests), __('User interests'));
    }
}
