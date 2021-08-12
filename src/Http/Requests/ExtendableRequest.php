<?php

namespace EscolaLms\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExtendableRequest extends FormRequest
{

    private static array $rules = [];

    public static function extendRules(array $rules): array
    {
        self::$rules += $rules;
        return self::$rules;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return self::$rules;        
    }
}
