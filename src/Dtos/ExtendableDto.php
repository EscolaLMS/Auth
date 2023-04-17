<?php

namespace EscolaLms\Auth\Dtos;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use Illuminate\Http\Request;

class ExtendableDto implements InstantiateFromRequest, DtoContract
{
    protected static $constructorTypes = [];
    protected static $returnTypes = [];

    /**
     * @param array $types of Callable
     * @return array $constructorTypes of Callable
     */
    public static function extendConstructor(array $types)
    {
        self::$constructorTypes += $types;
        return self::$constructorTypes;
    }

    /**
     * @param array $types of Callable
     * @return array $constructorTypes of Callable
     */
    public static function extendToArray(array $types)
    {
        self::$returnTypes += $types;
    }

    public static function instantiateFromRequest(Request $request): self
    {
        $value = new self();

        foreach (self::$constructorTypes as $key => $valueCallable) {
            $value->$key = $valueCallable($request);
        }

        return $value;
    }

    public function toArray(): array
    {
        return array_map(function ($returnType) {
            return $returnType($this);
        }, self::$returnTypes);
    }
}
