<?php

namespace EscolaLms\Auth\Models;

use Database\Factories\EscolaLms\Auth\Models\UserFactory;
use EscolaLms\Auth\Models\Traits\HasOnboardinngStatus;
use EscolaLms\Auth\Models\Traits\UserHasSettings;
use EscolaLms\Categories\Models\Traits\HasInterests;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends \EscolaLms\Core\Models\User
{
    use HasInterests, HasOnboardinngStatus, UserHasSettings;

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
