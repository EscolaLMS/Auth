<?php

namespace EscolaLms\Auth\Http\Requests;

class UploadAvatarRequest extends ExtendableRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('update', $this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'avatar' => ['required', 'file', 'mimes:png,jpg,jpeg,svg,gif'],
        ];
    }
}
