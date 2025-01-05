<?php

namespace Safronik\Models\Entities;

trait ObjectHelper
{
    public static function isValueObject(): bool
    {
        return ! ( self::class instanceof Entity );
    }

    public static function isEntity()
    {
        return self::class instanceof Entity;
    }
}