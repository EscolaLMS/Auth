<?php

namespace EscolaLms\Auth\Http\Controllers\Admin;

use EscolaLms\Auth\Exceptions\UserNotFoundException;
use EscolaLms\Auth\Http\Requests\Admin\AbstractUserIdInRouteRequest;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\UserGroupServiceContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;

class AbstractUserController extends EscolaLmsBaseController
{
    protected UserRepositoryContract $userRepository;
    protected UserServiceContract $userService;
    protected UserGroupServiceContract $userGroupService;

    public function __construct(UserRepositoryContract $userRepository, UserServiceContract $userService, UserGroupServiceContract $userGroupService)
    {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->userGroupService = $userGroupService;
    }

    protected function fetchRequestedUser(AbstractUserIdInRouteRequest $request): User
    {
        /** @var int $id */
        $id = $request->route('id');
        /** @var User|null $user */
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new UserNotFoundException();
        }
        return $user;
    }
}
