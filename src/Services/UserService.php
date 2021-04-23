<?php

namespace EscolaLms\Auth\Services;

use EscolaLms\Auth\Events\UserLogged;
use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Models\User as AuthUser;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Dtos\CriteriaDto;
use EscolaLms\Core\Dtos\PaginationDto;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserService implements UserServiceContract
{
    private UserRepositoryContract $userRepository;

    /**
     * UserService constructor.
     * @param UserRepositoryContract $userRepository
     */
    public function __construct(UserRepositoryContract $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function create(UserSaveDto $userSaveDto): User
    {
        $attributes['remember_token'] = Str::random(10);
        $user = $this->userRepository->create($userSaveDto->getUserAttributes());
        assert($user instanceof AuthUser);
        $this->assignRole($user, $userSaveDto->getRoles());
        return $user;
    }

    public function update(User $user, UserSaveDto $userSaveDto): User
    {
        $attributes['remember_token'] = Str::random(10);
        $this->userRepository->update($userSaveDto->getUserAttributes(), $user->id);
        $this->assignRole($user, $userSaveDto->getRoles());
        return $user;
    }

    public function login(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (is_null($user) || !Hash::check($password, $user->password)) {
            throw new Exception('Invalid credentials');
        }

        assert($user instanceof AuthUser);

        if (!$user->hasVerifiedEmail()) {
            throw new Exception('Email not validated');
        }

        if (!$user->is_active) {
            throw new Exception("User account has been deactivated");
        }

        event(new UserLogged($user));

        return $user;
    }

    public function deleteAvatar(User $user): bool
    {
        assert($user instanceof AuthUser);
        if (!empty($user->path_avatar)) {
            $result = Storage::delete('users/' . $user->path_avatar);
            $user->update(['path_avatar' => null]);
            return $result;
        }
        return false;
    }

    public function uploadAvatar(User $user, UploadedFile $avatar): ?string
    {
        assert($user instanceof AuthUser);
        if (empty($user->path_avatar)) {
            $user->path_avatar = Str::random(40) . '.' . $avatar->clientExtension();
        }
        if ($avatar->storeAs('users', $user->path_avatar)) {
            $user->save();
            return $user->avatar_url;
        }
        return null;
    }

    private function assignRole(User $user, array $roles): void
    {
        assert($user instanceof AuthUser);
        $user->roles()->detach();
        foreach ($roles as $role_name) {
            $user->assignRole($role_name);
        }
    }

    public function search(CriteriaDto $criteriaDto, PaginationDto $paginationDto): Collection
    {
        return $this->userRepository->searchByCriteria($criteriaDto->toArray(), $paginationDto->getSkip(), $paginationDto->getLimit());
    }
}
