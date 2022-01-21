<?php

namespace EscolaLms\Auth\Models;

use Database\Factories\EscolaLms\Auth\Models\UserFactory;
use EscolaLms\Auth\Events\EscolaLmsAccountBlockedTemplateEvent;
use EscolaLms\Auth\Events\EscolaLmsAccountDeletedTemplateEvent;
use EscolaLms\Auth\Models\Traits\HasGroups;
use EscolaLms\Auth\Models\Traits\HasOnboardingStatus;
use EscolaLms\Auth\Models\Traits\UserHasSettings;
use EscolaLms\Categories\Models\Traits\HasInterests;

/**
 * @OA\Schema(
 *     description="User model",
 *     title="User",
 *     required={},
 *     @OA\Xml(
 *         name="User"
 *     ),
 *     @OA\Property(
 *          type="integer",
 *          format="int64",
 *          property="id",
 *     ),
 *     @OA\Property(
 *          property="email",
 *          type="string"
 *     )
 * )
 */
class User extends \EscolaLms\Core\Models\User
{
    use HasInterests, HasOnboardingStatus, UserHasSettings;
    use HasGroups;

    public const PASSWORD_RULES = [
        'required',
        'string',
        'min:6'
    ];

    protected function getTraitOwner(): self
    {
        return $this;
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($user){
            event(new EscolaLmsAccountDeletedTemplateEvent($user));
        });

        static::updated(function($user){
            if ($user->wasChanged('is_active') && !$user->is_active) {
                event(new EscolaLmsAccountBlockedTemplateEvent($user));
            }
        });
    }
}
