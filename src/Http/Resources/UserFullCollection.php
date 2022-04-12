<?php

namespace EscolaLms\Auth\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserFullCollection extends ResourceCollection
{
    private array $columns = [];

    public function columns(?array $columns): UserFullCollection
    {
        $this->columns += array_merge($this->columns, $columns);
        return $this;
    }

    public function toArray($request)
    {
        return $this->collection
            ->map(fn (UserFullResource $resource) => $resource->columns($this->columns)->toArray($request))
            ->all();
    }
}
