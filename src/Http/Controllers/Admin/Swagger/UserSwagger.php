<?php

namespace EscolaLms\Auth\Http\Controllers\Admin\Swagger;

use EscolaLms\Auth\Http\Requests\Admin\UserAvatarDeleteRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserAvatarUploadRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserCreateRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserGetRequest;
use EscolaLms\Auth\Http\Requests\Admin\UsersListRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserDeleteRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserUpdateRequest;
use Illuminate\Http\JsonResponse;

interface UserSwagger
{
    /**
     * @OA\Get(
     *     path="/api/admin/users",
     *     summary="Get users",
     *     description="",
     *     tags={"Users"},
     *      security={
     *          {"passport": {}},
     *      },
     *     @OA\Parameter(
     *          name="",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *          ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation, returns list of Users",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  type="array",
     *                  @OA\Items(
     *                      $ref="#/components/schemas/User"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function listUsers(UsersListRequest $request): JsonResponse;

    /**
     * @OA\Get(
     *     path="/api/admin/users/{id}",
     *     summary="Get user",
     *     description="",
     *     tags={"Users"},
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
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation, returns User data",
     *          @OA\JsonContent($ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function getUser(UserGetRequest $request): JsonResponse;

    /**
     * @OA\Patch(
     *     path="/api/admin/user/{id}",
     *     summary="Update user",
     *     description="",
     *     tags={"Users"},
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
     *      ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Invalid id",
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="User not found",
     *     ),
     * )
     */
    public function partialUpdateUser(UserUpdateRequest $request): JsonResponse;

    /**
     * @OA\Put(
     *     path="/api/admin/user/{id}",
     *     summary="Update user",
     *     description="",
     *     tags={"Users"},
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
     *      ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Invalid id",
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="User not found",
     *     ),
     * )
     */
    public function updateUser(UserUpdateRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *     path="/api/admin/users",
     *     summary="Create user",
     *     description="",
     *     tags={"Users"},
     *      security={
     *          {"passport": {}},
     *      },
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation,  returns created User data",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     * )
     */
    public function createUser(UserCreateRequest $request): JsonResponse;

    /**
     * @OA\Delete(
     *     path="/api/admin/users/{id}",
     *     summary="Delete user",
     *     description="",
     *     tags={"Users"},
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
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Invalid id",
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="User not found",
     *     ),
     * )
     */
    public function deleteUser(UserDeleteRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      tags={"Users"},
     *      path="/api/admin/users/{id}/avatar",
     *      description="Upload avatar",
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
     *      ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="avatar", type="file", @OA\Items(type="string", format="binary")),
     *              )
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function uploadAvatar(UserAvatarUploadRequest $request): JsonResponse;

    /**
     * @OA\Delete(
     *      tags={"Users"},
     *      path="/api/admin/users/{id}/avatar",
     *      description="Delete avatar",
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
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function deleteAvatar(UserAvatarDeleteRequest $request): JsonResponse;
}
