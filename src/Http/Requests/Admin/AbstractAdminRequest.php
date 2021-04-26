<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Http\Requests\Traits\WithRole;
use EscolaLms\Core\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractAdminRequest extends FormRequest
{
    use WithRole;

    public function authorize()
    {
        return $this->hasRole(UserRole::ADMIN) && $this->user()->can('user manage');
    }
}
