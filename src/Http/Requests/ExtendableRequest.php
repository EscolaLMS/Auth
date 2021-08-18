<?php

namespace EscolaLms\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

abstract class ExtendableRequest extends FormRequest
{
    private static array $additional_rules = [];

    public static function extendRules(array $rules): array
    {
        self::$additional_rules += $rules;
        return self::$additional_rules;
    }

    /**
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function createDefaultValidator(ValidationFactory $factory)
    {
        return $factory->make(
            $this->validationData(),
            array_merge($this->container->call([$this, 'rules']), self::$additional_rules ?? []),
            $this->messages(),
            $this->attributes()
        )->stopOnFirstFailure($this->stopOnFirstFailure);
    }
}
