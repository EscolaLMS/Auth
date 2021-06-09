<?php

namespace EscolaLms\Auth\Http\Requests\Admin;

use EscolaLms\Auth\Enums\OnboardingStatus;
use EscolaLms\Core\Enums\StatusEnum;
use EscolaLms\Core\Enums\UserRole;
use BenSampo\Enum\Rules\EnumValue;

class UsersListRequest extends AbstractAdminRequest
{
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
