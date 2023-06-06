<?php

namespace EscolaLms\Auth\Services;

use EscolaLms\Auth\Dtos\Admin\UserAssignableDto;
use EscolaLms\Auth\Dtos\Admin\UserUpdateDto as AdminUserUpdateDto;
use EscolaLms\Auth\Dtos\Admin\UserUpdateKeysDto as AdminUserUpdateKeysDto;
use EscolaLms\Auth\Dtos\UserSaveDto;
use EscolaLms\Auth\Dtos\UserUpdateDto;
use EscolaLms\Auth\Dtos\UserUpdateKeysDto;
use EscolaLms\Auth\Dtos\UserUpdateSettingsDto;
use EscolaLms\Auth\Events\AccountConfirmed;
use EscolaLms\Auth\Events\Impersonate;
use EscolaLms\Auth\Events\Login;
use EscolaLms\Auth\Models\User as AuthUser;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Auth\Services\Contracts\UserServiceContract;
use EscolaLms\Core\Dtos\CriteriaDto;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Dtos\PaginationDto;
use EscolaLms\Files\Helpers\FileHelper;
use EscolaLms\ModelFields\Facades\ModelFields;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Http\FormRequest;
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
            event(new AccountConfirmed($user));
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
                    event(new AccountConfirmed($user));
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
                    event(new AccountConfirmed($user));
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
            event(new AccountConfirmed($user));
            $user->refresh();
        }

        if (!$user->hasVerifiedEmail()) {
            throw new Exception('Email not validated');
        }

        if (!$user->is_active) {
            throw new Exception("User account has been deactivated");
        }

        event(new Login($user));

        return $user;
    }

    public function impersonate(int $id): User
    {
        $user = $this->userRepository->find($id);

        assert($user instanceof AuthUser);

        event(new Impersonate($user));

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

    public function uploadAvatar(User $user, $avatar): ?User
    {
        assert($user instanceof AuthUser);
        $user->path_avatar = FileHelper::getFilePath($avatar, 'avatars/' . $user->id);
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

    public function searchAndPaginate(
        CriteriaDto $criteriaDto,
        ?array $columns = [],
        ?array $with = [],
        ?array $appends = [],
        ?int $perPage = null,
        ?int $page = null,
        ?OrderDto $orderDto = null
    ): LengthAwarePaginator
    {
        $columns = $this->makeColumns($columns);
        $with = $this->makeRelations($with);

        $query = $this->userRepository
            ->queryWithAppliedCriteria($criteriaDto->toArray())
            ->with($with);

        if (!is_null($orderDto) && !is_null($orderDto->getOrderBy())) {
            $query->orderBy($orderDto->getOrderBy(), $orderDto->getOrder() ?? 'asc');
        }

        return $query
            ->paginate($perPage, $columns, 'page', $page)
            ->appends($appends);
    }

    public function updateAdditionalFieldsFromRequest(User $user, FormRequest $request): void
    {
        $keys = ModelFields::getFieldsMetadata(AuthUser::class)->pluck('name');
        $fields = $request->collect()->only($keys)->toArray();
        $this->userRepository->update($fields, $user->getKey());
    }

    public function anonymiseEmail(AuthUser $user): void
    {
        $this->userRepository->update([
            'email' => $user->email . '+deleted+' . now()->timestamp,
        ], $user->getKey());
    }

    public function assignableUsers(CriteriaDto $dto, ?int $perPage = null, ?int $page = null): LengthAwarePaginator
    {
        return $this->searchAndPaginate($dto, [], [], [], $perPage, $page);
    }

    private function makeColumns(?array $columns): array
    {
        if (!$columns) {
            return ['*'];
        }

        $fields = ModelFields::getFieldsMetadata($this->userRepository->model())->pluck('name')->toArray();
        $columns[] = 'id';
        return array_diff($columns, $fields);
    }

    private function makeRelations(?array $relations): array
    {
        return ($relations ?? []) + ['roles', 'roles.permissions', 'permissions'];
    }
}
