<?php

namespace Safronik\Core\CodeTemplates;

trait Singleton
{
    /**
     * @var mixed
     */
    private static self $instance;
    
    /**
     * Constructor
     *
     * @param array $params Additional parameters to pass in the method initialize()
     *
     * @return mixed|\static
     */
    public static function getInstance( ...$params ): mixed
    {
        return self::$instance ?? self::$instance = new static( ...$params );
    }
    
    public static function isInitialized(): bool
    {
        return isset( static::$instance );
    }
}
