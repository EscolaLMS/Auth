<?php

namespace EscolaLms\Auth\Repositories;

use EscolaLms\Auth\Dtos\UserUpdateInterestsDto;
use EscolaLms\Auth\Dtos\UserUpdateSettingsDto;
use EscolaLms\Auth\Events\EscolaLmsPasswordChangedTemplateEvent;
use EscolaLms\Auth\Models\Traits\UserHasSettings;
use EscolaLms\Auth\Models\User as AuthUser;
use EscolaLms\Auth\Models\UserSetting;
use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Categories\Models\Traits\HasInterests;
use EscolaLms\Core\Repositories\BaseRepository;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

/**
 * Class CourseRatingRepository
 * @package EscolaLms\Auth\Repositories
 * @version December 1, 2020, 11:46 am UTC
 */
class UserRepository extends BaseRepository implements UserRepositoryContract
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return config('auth.providers.users.model', AuthUser::class);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->newQuery()->where('email', $email)->first();
    }

    public function findByEmailOrFail(string $email): ?User
    {
        $user = $this->model->newQuery()->where('email', $email)->firstOrFail();
        assert($user instanceof User);
        return $user;
    }

    public function findOrCreate(?int $id): User
    {
        if ($id) {
            return $this->find($id) ?? new $this->model();
        }
        return new $this->model();
    }

    public function search(?string $query): LengthAwarePaginator
    {
        $users = $this->model;
        if (!empty($query)) {
            $users = $users->where(function ($q) use ($query) {
                $q->where('first_name', 'LIKE', "%$query%")
                    ->orWhere('last_name', 'LIKE', "%$query%")
                    ->orWhere('email', 'LIKE', "%$query%");
            });
        }
        return $users->paginate(config('app.paginate_count'));
    }

    public function patchSettingsUsingDto(User $user, UserUpdateSettingsDto $dto): Collection
    {
        return $this->updateSettings($user,  $dto->toArray());
    }

    public function putSettingsUsingDto(User $user, UserUpdateSettingsDto $dto): Collection
    {
        return $this->setSettings($user, $dto->toArray());
    }

    public function setSettings(User $user, array $settings): Collection
    {
        $this->ensureUserHasSettingsTrait($user);

        /** @var AuthUser $user */
        $this->removeSettingsNotInArray($user, $settings);
        return $this->updateSettings($user, $settings);
    }

    public function updateSettings(User $user, array $settings): Collection
    {
        $this->ensureUserHasSettingsTrait($user);
        /** @var AuthUser $user */
        foreach ($settings as $key => $value) {
            $user->settings()->updateOrCreate([
                'key' => $key,
            ], [
                'value' => $value,
            ]);
        }
        return $user->settings;
    }

    private function removeSettingsNotInArray(User $user, array $settings): void
    {
        /** @var AuthUser $user */
        foreach ($user->settings as $setting) {
            /** @var UserSetting $setting */
            if (!in_array($setting->key, array_keys($settings))) {
                $setting->delete();
            }
        }
    }

    private function ensureUserHasSettingsTrait(User $class): void
    {
        if (!in_array(UserHasSettings::class, class_uses_recursive($class))) {
            throw new InvalidArgumentException("User Model must use Has Settings trait");
        }
    }

    public function addInterestById(User $user, int $interest_id): Collection
    {
        $this->ensureUserHasInterestsTrait($user);

        /** @var AuthUser $user */
        $user->interests()->attach($interest_id);
        return $user->interests;
    }

    public function removeInterestById(User $user, int $interest_id): Collection
    {
        $this->ensureUserHasInterestsTrait($user);

        /** @var AuthUser $user */
        $user->interests()->detach($interest_id);
        return $user->interests;
    }

    public function updateInterestsUsingDto(User $user, UserUpdateInterestsDto $dto): Collection
    {
        /** @var AuthUser $user */
        return $this->updateInterests($user, $dto->toArray());
    }

    public function updateInterests(User $user, array $interests): Collection
    {
        $this->ensureUserHasInterestsTrait($user);

        /** @var AuthUser $user */
        $user->interests()->sync($interests);
        return $user->interests;
    }

    private function ensureUserHasInterestsTrait(User $class): void
    {
        if (!in_array(HasInterests::class, class_uses_recursive($class))) {
            throw new InvalidArgumentException("User Model must use HasInterests trait");
        }
    }

    public function updatePassword(User $user, string $newPassword): bool
    {
        assert($user instanceof Model);
        if ($this->update(['password' => Hash::make($newPassword)], $user->getKey())) {
            event(new EscolaLmsPasswordChangedTemplateEvent($user));
            return true;
        }
        return false;
    }


}
