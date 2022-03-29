<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Http\Requests\ExtendableRequest;
use EscolaLms\Auth\Models\Group;
use Illuminate\Validation\Rule;

class UserGroupCreateRequest extends ExtendableRequest
{
    public function authorize()
    {
        return $this->user()->can('create', Group::class);
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
