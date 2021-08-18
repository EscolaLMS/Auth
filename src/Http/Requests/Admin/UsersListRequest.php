<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Enums\OnboardingStatus;
use EscolaLms\Core\Enums\StatusEnum;
use EscolaLms\Core\Enums\UserRole;
use BenSampo\Enum\Rules\EnumValue;
use EscolaLms\Auth\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UsersListRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('viewAny', User::class);
    }

    public function rules()
    {
        return [
            'search' => ['nullable'],
            'role' => ['nullable', new EnumValue(UserRole::class, false)],
            'status' => ['nullable', new EnumValue(StatusEnum::class, false)],
            'onboarding' => ['nullable', new EnumValue(OnboardingStatus::class, false)],
            'from' => ['date', 'nullable'],
            'to' => ['date', 'nullable']
        ];
    }
}
