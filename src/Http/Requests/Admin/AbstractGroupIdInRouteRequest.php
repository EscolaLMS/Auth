<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Models\Group;

abstract class AbstractGroupIdInRouteRequest extends AbstractAdminOnlyRequest
{
    protected function prepareForValidation()
    {
        parent::prepareForValidation();
        $this->merge(['id' => $this->route('id')]);
    }

    public function rules()
    {
        return [
            'id' => [
                'required',
                'exists:' . Group::query()->getQuery()->from . ',id'
            ]
        ];
    }

    public function getGroupFromRoute(): Group
    {
        return Group::query()->findOrFail($this->route('id'));
    }
}
