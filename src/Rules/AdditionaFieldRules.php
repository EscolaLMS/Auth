<?php

namespace EscolaLms\Auth\Rules;

use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use Illuminate\Support\Facades\Config;

class AdditionaFieldRules
{

    public static function rules()
    {
        $rules = [];
        foreach (Config::get(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.additional_fields', []) as $field) {
            $rules[$field] = ['nullable', 'string'];
        }
        foreach (Config::get(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.additional_fields_required', []) as $field) {
            $rules[$field] = ['required', 'string'];
        }

        return $rules;
    }
}
