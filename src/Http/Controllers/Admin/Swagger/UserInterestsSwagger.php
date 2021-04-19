<?php

namespace EscolaLms\Auth\Http\Controllers\Admin\Swagger;

use EscolaLms\Auth\Http\Requests\Admin\UserInterestAddRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserInterestDeleteRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserInterestsListRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserInterestsUpdateRequest;
use Illuminate\Http\JsonResponse;

interface UserInterestsSwagger
{
    /**
     * @OA\Get(
     *     path="/api/admin/users/{id}/interests",
     *     summary="Get user interests",
     *     description="",
     *     tags={"Users"},
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *          ),
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation, returns list of user interests",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  type="array",
     *                  @OA\Items(
     *                      $ref="#/components/schemas/Category"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function listUserInterests(UserInterestsListRequest $request): JsonResponse;

    public function updateUserInterests(UserInterestsUpdateRequest $request): JsonResponse;

    public function addUserInterest(UserInterestAddRequest $request): JsonResponse;

    public function deleteUserInterest(UserInterestDeleteRequest $request): JsonResponse;
}
