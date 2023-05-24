<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Http\Requests\ExtendableRequest;
use EscolaLms\Auth\Models\Group;

class UserGroupListRequest extends ExtendableRequest
{
    public function authorize()
    {
        return $this->user()->can('viewAny', Group::class);
    }

    public function rules()
    {
        return [
            'search' => ['sometimes', 'string'],
            'parent_id' => ['sometimes', 'integer'],
            'order_by' => ['sometimes', 'string', 'in:id,name,registerable,parent_name,created_at'],
            'order' => ['sometimes', 'string', 'in:ASC,DESC'],
        ];
    }
}
