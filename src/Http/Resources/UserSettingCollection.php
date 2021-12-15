<?php

namespace EscolaLms\Auth\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use EscolaLms\Auth\Traits\ResourceExtandable;

class UserSettingCollection extends ResourceCollection
{
    use ResourceExtandable;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $this->withoutWrapping();

        $fields = [];
        $parentFields = parent::toArray($request);
        foreach ($parentFields as $setting) {
            foreach ($setting as $key => $value) {
                $fields[$key] = $value;
            }
        }
        return self::apply($fields, $this);
    }
}
