<?php

namespace EscolaLms\Auth\Rules;

use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Config;

class AdditionalFieldsRequiredInConfig implements Rule, DataAwareRule
{
    protected $data = [];
    protected string $field;

    public function passes($attribute, $value)
    {

        if (array_key_exists('escola_auth__additional_fields', $this->data)) {
            $additional_fields = $this->data['escola_auth__additional_fields'];
            foreach ($value as $field) {
                if (!in_array($field, $additional_fields)) {
                    $this->field = $field;
                    return false;
                }
            }
        } else {
            $additional_fields = Config::get(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.additional_fields', []);
            foreach ($value as $field) {
                if (!in_array($field, $additional_fields)) {
                    $this->field = $field;
                    return false;
                }
            }
        }

        return true;
    }

    public function message()
    {
        return __('All required fields must exist on additional fields list. :field is missing.', ['field' => $this->field]);
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
