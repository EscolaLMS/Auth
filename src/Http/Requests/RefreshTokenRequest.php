<?php

namespace EscolaLms\Auth\Http\Requests;

class RefreshTokenRequest extends ExtendableRequest
{
    public function authorize(): bool
    {
        return !empty($this->user());
    }

    public function rules(): array
    {
        return [];
    }
}
