<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Categories\Models\Category;
use Illuminate\Http\Resources\Json\JsonResource;
use EscolaLms\Auth\Traits\ResourceExtandable;

class UserInterestResource extends JsonResource
{
    use ResourceExtandable;
    public function __construct(Category $resource)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        /** @var Category $resource */
        $resource = $this->resource;
        $array = $resource->toArray();
        unset($array['users']);
        return self::apply($array, $this);
    }
}
