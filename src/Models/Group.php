<?php

namespace EscolaLms\Auth\Models;

use Database\Factories\EscolaLms\Auth\Models\GroupFactory;
use EscolaLms\Auth\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 *          type="integer",
 *          format="int64",
 *          property="parent_id",
 *     ),
 *     @OA\Property(
 *          type="boolean",
 *          property="registerable",
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
 * @property-read Group|null $parent
 */
class Group extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'registerable' => 'bool'
    ];

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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Group::class, 'parent_id')->with('children');
    }

    public function getNameWithBreadcrumbsAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->generateBreadcrumbs([$this->getKey()]) . ucfirst($this->name);
        }
        return $this->name;
    }

    // There is no checking for cycles in parent<->child relation so for safety this method will stop concatenating names when a cycle is found
    protected function generateBreadcrumbs(array $included_ids = []): string
    {
        $result = '';
        if (!in_array($this->getKey(), $included_ids)) {
            $included_ids[] = $this->getKey();
            if ($this->parent) {
                $result .= $this->parent->generateBreadcrumbs($included_ids);
            }
            $result .= ucfirst($this->name) . '. ';
        }
        return $result;
    }
}
