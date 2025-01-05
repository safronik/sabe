<?php

namespace Safronik\Models\Entities\Extensions;

use JsonException;
use Safronik\Core\SanitizerHelper;
use Safronik\Core\ValidationHelper;
use Safronik\Models\Entities\Exceptions\EntityException;
use Safronik\Models\Entities\Obj;
use Safronik\Models\Entities\Rule;

/** @property Rule[] $rules */
trait ObjectHydrator{

    /**
     * Hydrate entity from values
     *
     * @param array $data
     *
     * @return void
     * @throws EntityException
     * @throws JsonException
     */
    public function hydrate( array $data = [] ): void
    {
        $this->validateProperties( $data );

        foreach( $data as $property => &$value ){

            static::hasProperty( $property )
                || throw new EntityException( "Trying to set undocumented property $property for " . static::class . ' object' );

            $this->prepareValue( $property, $value );

            $property_rule = static::$rules[ $property ];

            // Multiple property
            if( $property_rule->isMultiple() ){
                $this->$property = [];
                foreach( $value ?? [] as $value_item ){
                    $this->$property[] = new ($property_rule->type)( $value_item, true );
                }
                continue;
            }

            $this->$property = $property_rule->isScalar() ? $value : new ( $property_rule->type )( $value, true );
        }
    }

    private function validateProperties( $properties ): void
    {
        $rules = empty( $properties['id'] )
            ? static::rules( Obj::filterExceptFields( 'id' ) )
            : static::rules();

        ValidationHelper::validateRequired ( $properties, $rules ); // Validate required properties
        ValidationHelper::         validate( $properties, $rules ); // Validate by entity embedded rules
        ValidationHelper::validateRedundant( $properties, $rules ); // Validate redundant properties
        SanitizerHelper::sanitize          ($properties, $rules ); // Sanitize by entity SQL-schema rules
    }

    private function propertyExists( string $property ): bool
    {
        return isset( static::rules()[ $property ] );
    }
    
    private function prepareValue( string $property, mixed &$value ): void
    {
        // Custom preparation. Ignore all other preparations
        $custom_prepare_function_name = 'prepare' . ucfirst( $property );
        if( method_exists( $this, $custom_prepare_function_name ) ){
            $value = $this->$custom_prepare_function_name( $value );
            
        // Decode JSON if content is JSON
        }elseif( isset( static::$rules[ $property ]->content ) && static::$rules[ $property ]->content === 'json'){
            $value = json_decode( $value, true, 512, JSON_THROW_ON_ERROR );
        }
    }
}