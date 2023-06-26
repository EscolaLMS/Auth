<?php

namespace EscolaLms\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ProfileDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('delete', $this->user());
    }

    public function rules(): array
    {
        return [];
    }
}
