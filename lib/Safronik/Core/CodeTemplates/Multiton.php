<?php

namespace Safronik\Core\CodeTemplates;

trait Multiton
{
    protected static array $instances = [];
    
    public static function getInstance( ...$params ): static
    {
        if ( ! isset(static::$instances[ static::class ]) ) {
            static::$instances[ static::class ] = new static( ...$params );
        }

        return static::$instances[ static::class ];
    }
    
    public static function isInitialized(): bool
    {
        return isset( static::$instances[ static::class ] );
    }

}
