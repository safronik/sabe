<?php

namespace Safronik\Core\Validator;

use Safronik\Core\CodeTemplates\Interfaces\Installable;
use Safronik\Services\Options\Options;
use Safronik\Services\DBStructureHandler\DBStructureHandler;

class ClassValidator
{
    public function __construct(
        private string|object $class
    ){}
    
    public function implements( string|array $interfaces ): bool
    {
        $interfaces = (array) $interfaces;
    
        return array_reduce( $interfaces, function( $prev_res, $interface ){
            return
                $prev_res &&
                interface_exists( $interface ) &&
                in_array( $interface, (array) class_implements( $this->class, true ), true );
        }, true);
    }
}