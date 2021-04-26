<?php

namespace EscolaLms\Auth\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserSettingCollection extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $this->withoutWrapping();

        return parent::toArray($request) + (config('user') ?? []);
    }
}
