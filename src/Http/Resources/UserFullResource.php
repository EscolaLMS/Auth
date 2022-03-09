<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;

class UserFullResource extends UserResource
{
    public function toArray($request): array
    {
        return array_merge(
            parent::toArray($request),
            ModelFields::getExtraAttributesValues($this->resource, MetaFieldVisibilityEnum::ADMIN)
        );
    }
}
