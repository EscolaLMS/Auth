<?php

namespace EscolaLms\Auth\Models;

use Database\Factories\EscolaLms\Auth\Models\UserFactory;
use EscolaLms\Categories\Models\Traits\HasInterests;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends \EscolaLms\Core\Models\User
{
    use HasInterests;

    public function getOnboardingCompletedAttribute()
    {
        return $this->interests()->exists();
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }
}
