<?php

namespace EscolaLms\Auth\Dtos\Admin;

use EscolaLms\Auth\Repositories\Criteria\AssignableByCriterion;
use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\CriteriaDto;
use Illuminate\Support\Collection;

class UserAssignableDto extends CriteriaDto implements DtoContract
{
    public static function instantiateFromArray(array $array): self
    {
        $criteria = new Collection();

        if (key_exists('assignable_by', $array) && !is_null($array['assignable_by'])) {
            $criteria->push(new AssignableByCriterion($array['assignable_by']));
        }

        return new self($criteria);
    }
}
