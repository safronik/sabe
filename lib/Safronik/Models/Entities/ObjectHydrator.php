<?php

namespace Safronik\Models\Entities;

trait ObjectHydrator{
    
    /**
     * Hydrate entity from values
     *
     * @param object|array $properties
     *
     * @return void
     */
    public function hydrate( object|array $properties = [] ): void
    {
        foreach( (array)$properties as $property => &$value ){
            
            // Проверяем существует ли свойство
            // Если свойство скалярное или массив
            // Присваиваем значение
            // Устанавливаем тип
            // Если тип свойства объект, то вызываем её конструктор
            // Рекурсия
            
            // $reflection = new \ReflectionClass( static::class );
            // $target     = $reflection->newInstanceWithoutConstructor();
            //
            // $property = $reflection->getProperty($name);
            // $property->setAccessible(true);
            // $property->setValue($target, $value);
            
            if( property_exists( static::class, $property ) ){
                
                /** @var ValueObject $property_classname */
                $property_classname = $this->getPropertyEntityClassname( $property );
                
                // Complex property
                if( $this->isPropertyTypeComplex( $property ) ){
                    foreach( $value ?? [] as $sub_property_value ){
                        $this->$property[] = $property_classname
                            ? new $property_classname( $sub_property_value )
                            : $sub_property_value;
                    }
                    
                // Single property
                }else{
                    
                    $this->$property = $property_classname
                        ? new $property_classname( $value )
                        : $value;
                    
                }
            }
        }
    }
    
    /**
     * Checks if the property is complex
     *
     * @param string $property_name
     *
     * @return bool
     */
    private function isPropertyTypeComplex( string $property_name ): bool
    {
        $property_rule = static::$rules[ $property_name ];
        
        return str_ends_with( $property_rule['type'], '[]' ) ||
               in_array( 'multiple', $property_rule, true ) ||
               ( is_subclass_of( $property_rule['type'], ValueObject::class, false  ) && isset( $property_rule['length'] ) && $property_rule['length'] > 1 );
    }
    
    /**
     * Returns the classname of the complex property type
     *
     * @param string $property
     *
     * @return string|null
     */
    private function getPropertyEntityClassname( string $property ): ?string
    {
        $type = $this->isPropertyTypeComplex( $property )
            ? str_replace( '[]', '', static::$rules[ $property ]['type'] )
            : static::$rules[ $property ]['type'];
        
        return class_exists( $type )
            ? $type
            : null;
    }
}