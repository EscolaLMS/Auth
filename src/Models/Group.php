<?php

namespace EscolaLms\Auth\Models;

use Database\Factories\EscolaLms\Auth\Models\GroupFactory;
use EscolaLms\Auth\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @OA\Schema(
 *     description="Group model",
 *     title="Group",
 *     required={},
 *     @OA\Xml(
 *         name="Group"
 *     ),
 *     @OA\Property(
 *          type="integer",
 *          format="int64",
 *          property="id",
 *     ),
 *     @OA\Property(
 *          property="name",
 *          type="string"
 *     ),
 *     @OA\Property(
 *          property="users",
 *          type="array",
 *          @OA\Items(
 *              ref="#/components/schemas/User"
 *          )
 *     )
 * )
 * 
 * @property-read Collection $users
 */
class Group extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    protected $casts = [];

    protected static function newFactory()
    {
        return new GroupFactory();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(GroupUser::class);
    }

    public function belongsToUser(User $user): bool
    {
        return $this->relationLoaded('users')
            ? $this->users->contains('id', '=', $user->getKey())
            : $this->users()->wherePivot('user_id',  '=', $user->getKey())->exists();
    }
}
