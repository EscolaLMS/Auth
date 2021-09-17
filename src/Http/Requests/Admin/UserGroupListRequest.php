<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Models\Group;

class UserGroupListRequest extends AbstractAdminOnlyRequest
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
        ];
    }
}
