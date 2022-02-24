<?php

namespace EscolaLms\Auth\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;

class UserSettingValueCast implements CastsAttributes
{
    public function get($model, $key, $value, $attributes)
    {
        if (Str::startsWith($model->key, ['option_'])) {
            switch ($value) {
                case '1':
                case 'true':
                case 1:
                case true:
                    return true;
                default:
                    return false;
            }
        }
        return $value;
    }

    public function set($model, $key, $value, $attributes)
    {
        return $value;
    }
}
