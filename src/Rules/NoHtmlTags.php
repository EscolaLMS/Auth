<?php

namespace EscolaLms\Auth\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoHtmlTags implements Rule
{
    public function passes($attribute, $value)
    {
        return $value === strip_tags($value);
    }

    public function message()
    {
        return 'The :attribute must not contain HTML tags.';
    }
}
