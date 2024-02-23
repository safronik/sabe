<?php

namespace Safronik\Core\CodeTemplates;

trait Container
{
    use Singleton;
    
    protected array $services = [];
    protected array $aliases  = [];
    
    abstract protected function filterInitParameters( mixed $service, array &$params ): void;
    
    public function __construct( $services )
    {
        $this->appendBulk( $services );
    }
    
    public static function get( string $alias, mixed $params = [] ): mixed
    {
        $self  = static::isInitialized()
            ? static::getInstance()
            : throw new \Exception( 'Container ' . static::class . ' is not initialized yet. Please, do so before usage.' );
        
        $alias = $self->aliases[ $alias ] ?? $alias;
        
        return isset( $self->services[ $alias ] )
            ? $self->services[ $alias ]( $params )
            : throw new \Exception( "Service '$alias' not found Container" . static::class);
    }
    
    public static function has( string $service ): bool
    {
        return static::isInitialized()
            ? isset( self::getInstance()->services[ $service ] )
            : throw new \Exception(static::class . ' is not initialized yet. Please, do so.');
    }
    
    protected function appendBulk( array $services ): void
    {
        foreach( $services as $alias => $service_name){
            if( class_exists( $service_name ) ){
                $this->append( $service_name, is_string( $alias ) ? $alias : null );
            }
        }
    }
    
    private function append( string $service, string|null $alias = null ): void
    {
        $this->checkInterface( $service );
        $this->addAlias( $alias, $service );
        
        $using_singleton            = $this->isClassUseTrait( $service, Singleton::class );
        $this->services[ $service ] = function( $params ) use ( $using_singleton, $service ){
            
            $this->filterInitParameters( $service, $params );
            
            // Create or get an instance
            return $using_singleton
                ? $service::getInstance( ...$params )
                : new $service( ...$params );
        };
    }

    private function addAlias( string|null $alias, string|Service $service ): void
    {
        $alias = $alias ?? $service::getAlias();
        if( $alias ){
            $this->aliases[ $alias ] = $service;
        }
    }
    
    private function isClassUseTrait( string $classname, string $trait ): bool
    {
        return in_array( $trait, $this->getClassTraits( $classname ), true );
    }
    
    private function getClassTraits( string $classname ): array
    {
        $parentClasses = class_parents( $classname );
        $traits        = class_uses( $classname );
        
        foreach( $parentClasses as $parentClassname ){
            $traits = array_merge( $traits, class_uses( $parentClassname ) );
        }
        
        return $traits;
    }
    
    protected function filterClassesByInterface( array $classes, string $interface ): array
    {
        return array_filter( $classes, static fn( $service ) => in_array( $interface, class_implements( $service ), true ) );
    }
    
    private function checkInterface( $service ): void
    {
        if( isset( static::$interface_to_check ) ){
            // Check if the service implements obligatory interface
            $service_interfaces = (array)class_implements( $service, true );
            if( ! in_array( static::$interface_to_check, $service_interfaces, true ) ){
                throw new \Exception(
                    "Service $service does not implement " . static::$interface_to_check . ". Please do so. "
                );
            }
        }
    }

}