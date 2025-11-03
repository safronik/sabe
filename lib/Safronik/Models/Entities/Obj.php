<?php

namespace Safronik\Models\Entities;

use Safronik\Models\Entities\Exceptions\EntityException;
use Safronik\Models\Entities\Extensions\ObjectHydrator;
use Safronik\Models\Entities\Extensions\ObjectProperties;
use Safronik\Models\Entities\Extensions\Rules;

abstract class Obj{

    use Rules;
    use ObjectHydrator;
    use ObjectProperties;
    use ObjectHelper;

    /**
     * @throws EntityException
     * @throws \JsonException
     */
    public function __construct( $data = [] )
    {
        // Hydration
        $data
            && $this->hydrate( (array) $data );
        
        // Initialization with parameters
        method_exists( static::class, 'init')
            && $this->init();
    }
    
    public function toArray( $scalar_only = false ): array
    {
        $output = [];
        foreach( $this as $key => $value ){

            if( $scalar_only && ! is_scalar( $value ) ){
                continue;
            }

            $output[ $key ] = $value instanceof self
                ? $value->toArray()
                : $value;
        }
        
        return $output;
    }
    
    public function __isset( string $name ): bool
    {
        return isset( static::$rules[ $name ], $this->$name );
    }
    
    public function __get( string $name )
    {
        return $this->$name ?? null;
    }
    
    public function __set( string $name, $value ): void
    {
        property_exists( $this, $name )
            || throw new EntityException( "Could not set undocumented property $name for " . static::class . 'object' );
        
        $this->$name = $value;
    }
}