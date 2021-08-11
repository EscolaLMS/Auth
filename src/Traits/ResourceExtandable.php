<?php 

namespace EscolaLms\Auth\Traits;

trait ResourceExtandable {
    private static array $extensions = [];

    public static function extend(callable $extension)
    {
        self::$extensions[] = $extension;
    }

    
}