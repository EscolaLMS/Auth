<?php

namespace EscolaLms\Auth\Repositories;

use EscolaLms\Auth\Repositories\Contracts\UserRepositoryContract;
use EscolaLms\Core\Repositories\BaseRepository;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
    protected $fieldSearchable = [
    ];

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
        return config('auth.providers.users.model');
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->newQuery()->where('email', $email)->first();
    }

    public function findByEmailOrFail(string $email): ?User
    {
        return $this->model->newQuery()->where('email', $email)->firstOrFail();
    }

    public function findOrCreate(?int $id): User
    {
        if ($id) {
            return $this->find($id) ?: new config('auth.providers.users.model');
        }
        return new config('auth.providers.users.model');
    }

    public function updateInterests(array $interests, int $id): void
    {
        $user = $this->find($id);

        if ($user) {
            $user->interests()->sync($interests);
        }
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

    public function updateSettings(User $user, array $settings): void
    {
        foreach ($settings as $key => $value) {
            $user->settings()->updateOrInsert([
                'user_id' => $user->getKey(),
                'key' => $key,
            ], [
                'value' => $value,
            ]);
        }
    }

    public function updatePassword(User $user, string $newPassword): bool
    {
        return (bool)$this->update(['password' => Hash::make($newPassword)], $user->getKey());
    }
}
