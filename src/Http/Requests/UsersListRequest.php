<?php

namespace EscolaLms\Auth\Http\Requests;

use EscolaLms\Auth\Enums\OnboardingStatus;
use EscolaLms\Core\Enum\StatusEnum;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Auth\Http\Requests\Traits\WithRole;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;

class UsersListRequest extends FormRequest
{
    use WithRole;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->hasRole(UserRole::ADMIN);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
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
