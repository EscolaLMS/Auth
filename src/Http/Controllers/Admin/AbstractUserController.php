<?php

namespace EscolaLms\Auth\Http\Controllers\Admin;

use EscolaLms\Auth\Exceptions\UserNotFoundException;
use EscolaLms\Auth\Http\Requests\Admin\AbstractUserIdInRouteRequest;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;

class AbstractUserController extends EscolaLmsBaseController
{
    protected UserRepositoryContract $userRepository;
    protected UserServiceContract $userService;

    public function __construct(UserRepositoryContract $userRepository, UserServiceContract $userService)
    {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    protected function fetchRequestedUser(AbstractUserIdInRouteRequest $request): User
    {
        $user = $this->userRepository->find($request->route('id'));
        if (!$user) {
            throw new UserNotFoundException();
        }
        return $user;
    }

    // TODO: move this to EscolaLmsBaseController?
    public function sendResponseForResource(Request $request, JsonResource $resource, string $message = ''): JsonResponse
    {
        $wrappedResource = $resource->resource;
        if ($wrappedResource instanceof LengthAwarePaginator) {
            $meta = $wrappedResource->toArray();
            unset($meta['data']);
            return $this->sendResponseWithMeta($resource->toArray($request), $meta, $message);
        }
        if ($wrappedResource instanceof Model && $wrappedResource->wasRecentlyCreated) {
            return $this->sendCreatedResponse($resource->toArray($request), $message);
        }
        return $this->sendResponse($resource->toArray($request), $message);
    }

    // TODO: move this to EscolaLmsBaseController?
    public function sendResponseWithMeta(array $data, array $meta, string $message = ''): JsonResponse
    {
        return Response::json([
            'success' => true,
            'data'    => [
                'data' => $data,
                'meta' => $meta,
            ],
            'message' => $message,
        ]);
    }

    // TODO: move this to EscolaLmsBaseController or refactor ->sendResponse to accept status as argument?
    public function sendCreatedResponse(array $data, string $message = ''): JsonResponse
    {
        return Response::json([
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ], 201);
    }
}
