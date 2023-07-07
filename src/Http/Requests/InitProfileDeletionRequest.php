<?php

namespace EscolaLms\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class InitProfileDeletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('delete', $this->user());
    }

    public function rules(): array
    {
        return [
            'return_url' => ['required', 'string'],
        ];
    }

    public function getReturnUrl(): ?string
    {
        return $this->input('return_url');
    }
}
