<?php

namespace EscolaLms\Auth\Models;

use Database\Factories\EscolaLms\Auth\Models\UserFactory;
use EscolaLms\Auth\Models\Traits\HasOnboardingStatus;
use EscolaLms\Auth\Models\Traits\UserHasSettings;
use EscolaLms\Categories\Models\Traits\HasInterests;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    protected function getTraitOwner(): self
    {
        return $this;
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
