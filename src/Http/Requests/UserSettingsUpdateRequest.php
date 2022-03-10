<?php

namespace EscolaLms\Auth\Http\Requests;

use Illuminate\Support\Str;

class UserSettingsUpdateRequest extends ExtendableRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('updateSettings', $this->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // '*' => 'string' // this is pointless 
        ];
    }

    public function getSettingsWithoutAdditionalFields(): array
    {
        return array_filter($this->all(), fn ($key) => !Str::startsWith($key, 'additional_field:'), ARRAY_FILTER_USE_KEY);
    }
}
