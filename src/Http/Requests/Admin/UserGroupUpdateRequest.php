<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Models\Group;
use Illuminate\Validation\Rule;

class UserGroupUpdateRequest extends AbstractGroupIdInRouteRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->getGroupFromRoute());
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'parent_id' => ['nullable', 'integer', Rule::exists((new Group())->getTable(), (new Group())->getKeyName())],
            'registerable' => ['boolean'],
        ];
    }
}
