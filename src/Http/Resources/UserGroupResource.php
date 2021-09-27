<?php

namespace EscolaLms\Auth\Http\Resources;

use EscolaLms\Auth\Models\Group;
use Illuminate\Http\Resources\Json\JsonResource;

class UserGroupResource extends JsonResource
{
    public function __construct(Group $group)
    {
        parent::__construct($group);
    }

    public function getResource(): Group
    {
        return $this->resource;
    }

    public function toArray($request)
    {
        return [
            'id' => $this->getResource()->getKey(),
            'name' => $this->getResource()->name,
            'parent_id' => $this->getResource()->parent_id,
            'registerable' => $this->getResource()->registerable,
            'name_with_breadcrumbs' => $this->getResource()->name_with_breadcrumbs,
        ];
    }
}
