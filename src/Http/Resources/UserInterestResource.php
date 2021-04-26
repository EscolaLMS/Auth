<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Categories\Models\Category;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInterestResource extends JsonResource
{
    public function __construct($resource)
    {
        assert($resource instanceof Category);
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        /** @var Category $resource */
        $resource = $this->resource;
        $array = $resource->toArray();
        unset($array['users']);
        return $array;
    }
}
