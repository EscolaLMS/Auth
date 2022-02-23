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
     *     tags={"Admin User Interests"},
     *      security={
     *          {"passport": {}},
     *      },
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
     *                      ref="#/components/schemas/Category"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function listUserInterests(UserInterestsListRequest $request): JsonResponse;

    /**
     * @OA\Put(
     *     path="/api/admin/users/{id}/interests",
     *     summary="Set user interests",
     *     description="Set user interests",
     *     tags={"Admin User Interests"},
     *     security={
     *          {"passport": {}},
     *     },
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *          ),
     *     ),
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="interests",
     *                  type="array",
     *                  description="Ids of categories to be set",
     *                  @OA\Items(
     *                      type="integer",
     *                      format="int64"
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation, returns list of user interests",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Category"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function updateUserInterests(UserInterestsUpdateRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *     path="/api/admin/users/{id}/interests",
     *     summary="Add single user interest",
     *     description="Add single user interest",
     *     tags={"Admin User Interests"},
     *      security={
     *          {"passport": {}},
     *      },
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *          ),
     *     ),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *              required={"interest_id"},
     *                  @OA\Property(
     *                      property="interest_id",
     *                      type="integer",
     *                      format="int64",
     *                      description="Id of category to be added"
     *                  )
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation, returns list of user interests",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Category"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function addUserInterest(UserInterestAddRequest $request): JsonResponse;

    /**
     * @OA\Delete(
     *     path="/api/admin/users/{id}/interests/{interest_id}",
     *     summary="Add single user interest",
     *     description="",
     *     tags={"Admin User Interests"},
     *      security={
     *          {"passport": {}},
     *      },
     *     @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *          ),
     *     ),
     *     @OA\Parameter(
     *          name="interest_id",
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
     *                      ref="#/components/schemas/Category"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function deleteUserInterest(UserInterestDeleteRequest $request): JsonResponse;
}
