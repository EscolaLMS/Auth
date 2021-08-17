<?php

namespace EscolaLms\Auth\Http\Requests;

class UpdateInterests extends ExtendableRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('updateInterests', $this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'interests' => ['required', 'array', 'min:1'],
            'interests.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
