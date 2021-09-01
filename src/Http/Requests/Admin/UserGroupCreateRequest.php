<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Models\Group;

class UserGroupCreateRequest extends AbstractAdminOnlyRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Group::class);
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string']
        ];
    }
}
