<?php

namespace EscolaLms\Auth\Models\Traits;

use EscolaLms\Auth\Models\UserSetting;
use EscolaLms\Auth\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserHasSettings
{
    use ExtendableUserModelTrait;

    public function settings(): HasMany
    {
        /** @var User $this */
        return $this->hasMany(UserSetting::class);
    }
}
