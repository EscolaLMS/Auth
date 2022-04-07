<?php

namespace EscolaLms\Auth\Http\Controllers\Swagger;

use EscolaLms\Auth\Http\Requests\ProfileUpdateAuthDataRequest;
use EscolaLms\Auth\Http\Requests\ProfileUpdatePasswordRequest;
use EscolaLms\Auth\Http\Requests\ProfileUpdateRequest;
use EscolaLms\Auth\Http\Requests\UploadAvatarRequest;
use EscolaLms\Auth\Http\Requests\MyProfileRequest;
use EscolaLms\Auth\Http\Requests\UpdateInterests;
use EscolaLms\Auth\Http\Requests\UserSettingsUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface ProfileSwagger
{
    /**
     * @OA\Get(
     *      path="/api/profile/me",
     *      description="Get logged user info",
     *      tags={"Profile"},
     *      security={
     *          {"passport": {}},
     *      },
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
    public function me(MyProfileRequest $request): JsonResponse;

    /**
     * @OA\Put(
     *      path="/api/profile/me",
     *      description="Update the profile",
     *      tags={"Profile"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="first_name",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="last_name",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="age",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="gender",
     *          required=false,
     *          description="1 - male, 2 - female",
     *          in="query",
     *          @OA\Schema(
     *              type="number",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="country",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="city",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="street",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="postcode",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="phone",
     *          required=false,
     *          description="phone",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
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
    public function update(ProfileUpdateRequest $request): JsonResponse;

    /**
     * @OA\Put(
     *      tags={"profile"},
     *      path="/api/profile/me-auth",
     *      description="Update the profile authorization data",
     *      tags={"Profile"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="email",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
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
    public function updateAuthData(ProfileUpdateAuthDataRequest $request): JsonResponse;

    /**
     * @OA\Put(
     *      path="/api/profile/password",
     *      description="Update password",
     *      tags={"Profile"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="current_password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="new_password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="new_confirm_password",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
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
    public function updatePassword(ProfileUpdatePasswordRequest $request): JsonResponse;

    /**
     * @OA\Put(
     *     path="/api/profile/interests",
     *     summary="Update user interests",
     *     tags={"Profile"},
     *     security={
     *          {"passport": {}},
     *      },
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="interests",
     *                 description="Array categories ids",
     *                 type="array",
     *                 @OA\Items(
     *                     type="number",
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *     )
     * )
     */
    public function interests(UpdateInterests $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/profile/upload-avatar",
     *      description="Upload avatar",
     *      tags={"Profile"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="multipart/form-data",
    *              @OA\Schema(ref="#/components/schemas/UserAvatar")
    *          )
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
    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse;

    /**
     * @OA\Delete(
     *      path="/api/profile/delete-avatar",
     *      description="Delete avatar",
     *      tags={"Profile"},
     *      security={
     *          {"passport": {}},
     *      },
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
    public function deleteAvatar(Request $request): JsonResponse;

    /**
     * @OA\Get(
     *      path="/api/profile/settings",
     *      description="Get user settings",
     *      tags={"Profile"},
     *      security={
     *          {"passport": {}},
     *      },
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
    public function settings(Request $request): JsonResponse;

    /**
     * @OA\Put(
     *     path="/api/profile/settings",
     *     summary="Update user settings",
     *     tags={"Profile"},
     *     security={
     *          {"passport": {}},
     *      },
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="setting-key",
     *                 description="Example setting",
     *                 type="object",
     *                 example="example-value",
     *             ),
     *             @OA\Property(
     *                 property="setting-key2",
     *                 description="Example setting",
     *                 type="object",
     *                 example="example-value2",
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          )
     *     )
     * )
     */
    public function settingsUpdate(UserSettingsUpdateRequest $request): JsonResponse;
}
