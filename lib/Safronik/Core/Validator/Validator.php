<?php

namespace Safronik\Core\Validator;

use Safronik\Core\CodeTemplates\Singleton;

class Validator
{
    use Singleton;
    
    private array $validator = [];
    
    public static function init()
    {
        return self::getInstance();
    }
    
    public function string( array|string $string )
    {
    
    }
    
    public function int( array|int $int )
    {
    
    }
    
    public function class( string|object $class ): ClassValidator
    {
        return new ClassValidator( $class );
    }

    public function app( string|object $class ): AppValidator
    {
        return new AppValidator( $class );
    }
}