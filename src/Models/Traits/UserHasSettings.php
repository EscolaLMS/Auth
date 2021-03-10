<?php

namespace EscolaLms\Auth\Models\Traits;

use EscolaLms\Auth\Models\UserSetting;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait UserHasSettings
{
    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }
}
