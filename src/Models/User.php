<?php

namespace EscolaLms\Auth\Models;

use Database\Factories\EscolaLms\Auth\Models\UserFactory;
use EscolaLms\Categories\Models\Traits\HasInterests;

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
}
