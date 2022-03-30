<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Auth\Models\User;
use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;

class UserFullResource extends UserResource
{
    private array $columns = [];

    public function __construct(User $resource)
    {
        parent::__construct($resource);
    }

    public function columns(array $columns): UserFullResource
    {
        $this->columns = $columns;
        return $this;
    }

    public function toArray($request): array
    {
        if (!$this->columns) {
            return array_merge(
                parent::toArray($request),
                ModelFields::getExtraAttributesValues($this->resource, MetaFieldVisibilityEnum::ADMIN)
            );
        }

        return $this->getResource();
    }

    private function getResource(): array
    {
        $result = [];
        foreach ($this->resource->attributesToArray() as $key => $value) {
            if (in_array($key, $this->columns)) {
                $result[$key] = $value;
            }
        }

        $result += array_filter(
            ModelFields::getExtraAttributesValues($this->resource, MetaFieldVisibilityEnum::ADMIN),
            fn ($key) =>in_array($key, $this->columns),
            ARRAY_FILTER_USE_KEY
        );

        return $result;
    }
}
