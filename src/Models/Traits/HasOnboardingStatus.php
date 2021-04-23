<?php

namespace EscolaLms\Auth\Models\Traits;

/**
 * Trait HasOnboardinngStatus
 * @package EscolaLms\Auth\Models\Traits
 *
 * TODO:
 * This trait for now only checks if user has any interest (categories) added
 * In future we should use specification pattern allowing to create any number of rules by injecting
 */
trait HasOnboardingStatus
{
    use ExtendableModelTrait;

    public function getOnboardingCompletedAttribute()
    {
        return count($this->getTraitOwner()->interests);
    }
}
