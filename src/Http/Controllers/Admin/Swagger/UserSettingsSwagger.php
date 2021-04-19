<?php

namespace EscolaLms\Auth\Http\Controllers\Admin\Swagger;

use EscolaLms\Auth\Http\Requests\Admin\UserSettingsListRequest;
use EscolaLms\Auth\Http\Requests\Admin\UserSettingsUpdateRequest;
use Illuminate\Http\JsonResponse;

interface UserSettingsSwagger
{
    /**
     * @OA\Get(
     *     path="/api/admin/users/{id}/settings",
     *     summary="Get user settings",
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
     *          description="successful operation, returns list of user settings",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  type="array",
     *                  @OA\Items(
     *                      $ref="#/components/schemas/UserSetting"
     *                  )
     *              )
     *          )
     *     ),
     * )
     */
    public function listUserSettings(UserSettingsListRequest $request): JsonResponse;

    public function patchUserSettings(UserSettingsUpdateRequest $request): JsonResponse;

    public function putUserSettings(UserSettingsUpdateRequest $request): JsonResponse;
}
