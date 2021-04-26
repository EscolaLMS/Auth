<?php

namespace EscolaLms\Auth\Http\Controllers\Admin;

use EscolaLms\Auth\Exceptions\UserNotFoundException;
use EscolaLms\Auth\Http\Requests\Admin\AbstractUserIdInRouteRequest;
use EscolaLms\Auth\Models\User;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;

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
}
