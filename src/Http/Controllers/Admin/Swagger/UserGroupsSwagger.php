<?php

namespace EscolaLms\Auth\Http\Controllers\Admin\Swagger;

use EscolaLms\Auth\Http\Requests\Admin\UserGroupCreateRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserGroupDeleteRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserGroupGetRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserGroupListRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserGroupMemberAddRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserGroupMemberRemoveRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserGroupUpdateRequest;
use Illuminate\Http\JsonResponse;

interface UserGroupsSwagger
{
    /**
     * @OA\Get(
     *     path="/api/admin/user-groups/",
     *     summary="List groups",
     *     description="",
     *     tags={"Admin User Groups"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="search",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="parent_id",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="user_id",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation, returns list of groups",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Group"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function listGroups(UserGroupListRequest $request): JsonResponse;

    /**
     * @OA\Get(
     *     path="/api/admin/user-groups/tree/",
     *     summary="List groups in a tree",
     *     description="",
     *     tags={"Admin User Groups"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="search",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="parent_id",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *          ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation, returns list of groups",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Group"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function listGroupsTree(UserGroupListRequest $request): JsonResponse;

    /**
     * @OA\Get(
     *     path="/api/admin/user-groups/users",
     *     summary="List of groups with users",
     *     description="",
     *     tags={"Admin User Groups"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="search",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="parent_id",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="user_id",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          ),
     *      ),
     *     @OA\Parameter(
     *          name="id[]",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer"
     *              )
     *          ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation, returns list of groups",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/Group"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function listWithUsers(UserGroupListRequest $request): JsonResponse;

    /**
     * @OA\Get(
     *     path="/api/admin/user-groups/{id}",
     *     summary="Get group details",
     *     description="",
     *     tags={"Admin User Groups"},
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
     *          description="successful operation, returns Group data",
     *          @OA\JsonContent(ref="#/components/schemas/Group")
     *     ),
     * )
     */
    public function getGroup(UserGroupGetRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *     path="/api/admin/user-groups/",
     *     summary="Create group",
     *     description="",
     *     tags={"Admin User Groups"},
     *      security={
     *          {"passport": {}},
     *      },
     *     @OA\RequestBody(
     *         @OA\JsonContent(ref="#/components/schemas/Group")
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="successful operation, Group created",
     *          @OA\JsonContent(ref="#/components/schemas/Group")
     *     ),
     * )
     */
    public function createGroup(UserGroupCreateRequest $request): JsonResponse;

    /**
     * @OA\Put(
     *     path="/api/admin/user-groups/{id}",
     *     summary="Update group",
     *     description="",
     *     tags={"Admin User Groups"},
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
     *         @OA\JsonContent(ref="#/components/schemas/Group")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/Group")
     *     ),
     *     @OA\Response(
     *          response=404,
     *          description="Group not found",
     *     ),
     * )
     */
    public function updateGroup(UserGroupUpdateRequest $request): JsonResponse;

    /**
     * @OA\Delete(
     *     path="/api/admin/user-groups/{id}",
     *     summary="Delete group",
     *     description="",
     *     tags={"Admin User Groups"},
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
     *          description="successful operation, Group deleted"
     *     ),
     * )
     */
    public function deleteGroup(UserGroupDeleteRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *     path="/api/admin/user-groups/{id}/members",
     *     summary="Add user to group",
     *     description="",
     *     tags={"Admin User Groups"},
     *      security={
     *          {"passport": {}},
     *      },
     *     @OA\RequestBody(
     *              @OA\Property(
     *                  property="user_id",
     *                  type="integer",
     *                  format="int64",
     *                  description="Id of user to be added"
     *              )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation, returns list of group members",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/User"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function addMember(UserGroupMemberAddRequest $request): JsonResponse;

    /**
     * @OA\Delete(
     *     path="/api/admin/user-groups/{id}/members/{user_id}",
     *     summary="Add user to group",
     *     description="",
     *     tags={"Admin User Groups"},
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
     *     @OA\Parameter(
     *          name="user_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *              format="int64",
     *          ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation, returns list of group members",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  type="array",
     *                  @OA\Items(
     *                      ref="#/components/schemas/User"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function removeMember(UserGroupMemberRemoveRequest $request): JsonResponse;
}
