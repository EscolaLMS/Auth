<?php

namespace EscolaLms\Auth\Services;

use EscolaLms\Auth\Dtos\Admin\UserUpdateDto as AdminUserUpdateDto;
use EscolaLms\Auth\Dtos\Admin\UserUpdateKeysDto as AdminUserUpdateKeysDto;
use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Dtos\UserUpdateDto;
use EscolaLms\Auth\Dtos\UserUpdateKeysDto;
use EscolaLms\Auth\Dtos\UserUpdateSettingsDto;
use EscolaLms\Auth\Events\EscolaLmsLoginTemplateEvent;
use EscolaLms\Auth\Events\UserLogged;
use EscolaLms\Auth\Models\User as AuthUser;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Dtos\CriteriaDto;
use EscolaLms\Core\Dtos\PaginationDto;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
        /** @var \EscolaLms\Auth\Models\User $user */
        $user = $this->userRepository->create($userSaveDto->getUserAttributes());
        if ($user instanceof MustVerifyEmail && $userSaveDto->getVerified()) {
            $user->markEmailAsVerified();
        }
        assert($user instanceof User);
        $this->syncRoles($user, $userSaveDto->getRoles());
        return $user;
    }

    public function createWithSettings(UserSaveDto $userSaveDto, UserUpdateSettingsDto $userSettingsDto): User
    {
        $user = $this->create($userSaveDto);
        $this->userRepository->putSettingsUsingDto($user, $userSettingsDto);
        return $user;
    }

    public function update(User $user, UserSaveDto $userSaveDto): User
    {
        $this->userRepository->update($userSaveDto->getUserAttributes(), $user->id);
        $this->syncRoles($user, $userSaveDto->getRoles());
        return $user;
    }

    public function putUsingDto(UserUpdateDto $dto, int $id): User
    {
        $data = $dto->toArray();

        $user = $this->userRepository->update($data, $id);

        assert($user instanceof AuthUser);
        if ($dto instanceof AdminUserUpdateDto) {
            if (!is_null($dto->getEmailVerified())) {
                if ($dto->getEmailVerified() && !$user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }
                if (!$dto->getEmailVerified()) {
                    $user->email_verified_at = null;
                    $user->save();
                }
            }
            if ($dto->getRoles() !== null) {
                $this->syncRoles($user, $dto->getRoles());
            }
        }

        return $user;
    }

    public function patchUsingDto(UserUpdateDto $dto, UserUpdateKeysDto $keysDto, int $id): User
    {
        $data = $dto->toArray();

        $user = $this->userRepository->update($data, $id);

        assert($user instanceof AuthUser);
        if ($dto instanceof AdminUserUpdateDto && $keysDto instanceof AdminUserUpdateKeysDto) {
            if (!is_null($dto->getEmailVerified())) {
                if ($dto->getEmailVerified() && !$user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }
                if (!$dto->getEmailVerified()) {
                    $user->email_verified_at = null;
                    $user->save();
                }
            }
            if ($dto->getRoles() !== null && $keysDto->getRoles()) {
                $this->syncRoles($user, $dto->getRoles());
            }
        }
        return $user;
    }

    public function login(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (is_null($user) || !Hash::check($password, $user->password)) {
            throw new Exception('Invalid credentials');
        }

        assert($user instanceof AuthUser);

        if (!$user->hasVerifiedEmail() && $this->checkIfSuperadmin($user->getEmailForVerification())) {
            $user->markEmailAsVerified();
            $user->refresh();
        }

        if (!$user->hasVerifiedEmail()) {
            throw new Exception('Email not validated');
        }

        if (!$user->is_active) {
            throw new Exception("User account has been deactivated");
        }

        event(new UserLogged($user), new EscolaLmsLoginTemplateEvent($user));

        return $user;
    }

    private function checkIfSuperadmin(string $email): bool
    {
        $superadmins = array_filter(config('escola_auth.superadmins', []), fn ($item) => !empty($item));
        return in_array($email, $superadmins);
    }

    public function deleteAvatar(User $user): bool
    {
        assert($user instanceof AuthUser);
        if (!empty($user->path_avatar)) {
            $result = Storage::delete($user->path_avatar);
            $user->update(['path_avatar' => null]);
            return $result;
        }
        return false;
    }

    public function uploadAvatar(User $user, UploadedFile $avatar): ?User
    {
        assert($user instanceof AuthUser);
        $user->path_avatar = $avatar->store('avatars/' . $user->id);
        $user->save();
        return $user;
    }

    private function syncRoles(User $user, array $roles): void
    {
        assert($user instanceof AuthUser);
        $user->syncRoles($roles);
    }

    public function search(CriteriaDto $criteriaDto, PaginationDto $paginationDto): Collection
    {
        return $this->userRepository->searchByCriteria($criteriaDto->toArray(), $paginationDto->getSkip(), $paginationDto->getLimit());
    }

    public function searchAndPaginate(CriteriaDto $criteriaDto, array $appends = [], int $perPage = null, int $page = null): LengthAwarePaginator
    {
        return $this->userRepository->queryWithAppliedCriteria($criteriaDto->toArray())->paginate($perPage, ['*'], 'page', $page)->appends($appends);
    }
}
