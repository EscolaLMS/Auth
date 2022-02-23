<?php

namespace EscolaLms\Auth\Rules;

use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class AdditionaFieldRules
{

    public static function rules()
    {
        $rules = [];

        foreach (Config::get(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.additional_fields', []) as $field) {
            $rules[$field] = ['nullable'];
        }

        foreach (Config::get(EscolaLmsAuthServiceProvider::CONFIG_KEY . '.additional_fields_required', []) as $field) {
            $rules[$field] = ['required'];
        }

        foreach ($rules as $field => $arr) {
            if (Str::startsWith($field, ['option_'])) {
                $rules[$field][] = 'boolean';
            } else {
                $rules[$field][] = 'string';
            }
        }

        return $rules;
    }
}
