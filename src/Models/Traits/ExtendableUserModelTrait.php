<?php

namespace EscolaLms\Auth\Models\Traits;

use EscolaLms\Auth\Models\User;

trait ExtendableUserModelTrait
{
    abstract protected function getTraitOwner(): User;
}
