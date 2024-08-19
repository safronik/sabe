<?php

namespace Safronik\Models\Entities;

use Safronik\CodePatterns\Structural\Hydrator;

abstract class ValueObject{
    
    use ObjectHydrator;
    
    public static array $rules = [];
    
    public function __construct( $params = [] )
    {
        $params
            && $this->hydrate( (array) $params );
    
        method_exists( static::class, 'init')
            && static::init( $params );
    }
    
    public static function getRules()
    {
        return static::$rules;
    }
    
    public static function getRulesWithout( $rules_names = [] ): array
    {
        $rules = static::$rules;
        foreach( (array)$rules_names as $rule_name ){
            unset( $rules[ $rule_name ] );
        }
        
        return $rules;
    }
    
    public static function getRulesWithoutRequired(): array
    {
        return array_map( static function( $rule ){
            unset( $rule[ array_search( 'required', $rule, true ) ] );
            
            return $rule;
        }, static::$rules );
    }
    
    public static function ruleIsSubEntity( $rule, ?string $entity_class = null ): bool
    {
        $entity_class ??= static::class;
        
        return class_exists( $rule['type'] ) &&
            is_subclass_of( $rule['type'], EntityObject::class ) &&
            isset( $rule['length'] ) && $rule['length'] > 1 &&
            $entity_class !== $rule['type'];
    }
    
    public static function ruleIsSubObject( $rule, ?string $entity_class = null ): bool
    {
        $entity_class ??= static::class;
        
        return class_exists( $rule['type'] ) &&
            is_subclass_of( $rule['type'], ValueObject::class ) &&
            ! is_subclass_of( $rule['type'], EntityObject::class ) &&
            isset( $rule['length'] ) && $rule['length'] > 1 &&
            $entity_class !== $rule['type'];
    }
    
    public function toArray(): array
    {
        $output = [];
        foreach( $this as $key => $value ){
            $output[ $key ] = $value;
        }
        
        return $output;
    }
    
    //
    // public function toArray(): array
    // {
    //     return (array) $this->storage;
    // }
    //
    // /**
    //  * Get changed values
    //  *
    //  * @return array
    //  */
    // public function getChanges(): array
    // {
    //     // Get rid of dynamic properties
    //     $intersection = array_uintersect_assoc(
    //         $this->storage,
    //         $this->_initial_storage,
    //         function($a, $b){
    //             return 0;
    //         }
    //     );
    //
    //     $initial = $this->_initial_storage;
    //
    //     // Returns only difference
    //     return array_filter(
    //         $intersection,
    //         function( $val, $key ){
    //
    //             // Convert to string if valueObject provide such opportunity
    //             $val = ! is_scalar( $val ) && ! is_null( $val ) && method_exists( get_class( $val ), '_serialize' )
    //                 ? $val->_serialize()
    //                 : $val;
    //
    //             return array_key_exists( $key, $this->_initial_storage ) && $val != $this->_initial_storage[ $key ];
    //         },
    //         ARRAY_FILTER_USE_BOTH
    //     );
    // }
    //
    // public function toObject(): object
    // {
    //     return (object) $this->storage;
    // }
    //
    // public function __get( $name )
    // {
    //     return $this->storage[$name] ?? null;
    // }
    //
    // public function __set( $name, $value )
    // {
    //     $this->storage[$name] = $value;
    // }
    //
    // public function __isset( $name )
    // {
    //     return isset( $this->storage[ $name ] );
    // }
}