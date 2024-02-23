<?php

namespace Safronik\Services\Options;

// Interfaces
use Safronik\Core\CodeTemplates\Installer;
use Safronik\Core\CodeTemplates\Interfaces\Installable;
use Safronik\Core\CodeTemplates\Interfaces\Serviceable;
use Safronik\Core\CodeTemplates\Service;

// Templates

class Options implements Serviceable, Installable
{
    use Service, Installer;
    
    protected static string $service_alias = 'options';
    protected static string $gateway_alias = 'options';
    
    protected ?array  $options_to_load = [];
    protected array   $options_loaded  = [];
    protected array   $options_storage = [];
    protected ?string $options_group   = '';
    
    private DBOptionGatewayInterface $gateway;
    
    public function __construct( DBOptionGatewayInterface $gateway, ?string $options_group = null, ?array $options_to_load = null )
    {
        $this->gateway         = $gateway;
        $this->options_group   = $options_group;
        $this->options_to_load = $options_to_load ?? $this->gateway->getOptionsByGroup( $this->options_group );
        
        $this->options_group && $this->options_to_load && $this->loadOptions();
    }
    
    public function loadOptions(): void
    {
        ! $this->options_group   && throw new \Exception( "No slug was given" );
        ! $this->options_to_load && throw new \Exception( "No options were given" );
        
        // Load given options
		foreach ( $this->options_to_load as $option ){
			$this->loadOption( $option, $this->options_group );
		}
        
        // Get options failed to load
        $this->options_to_load = array_diff( $this->options_to_load, $this->options_loaded );
    }
    
	private function loadOption( $option, $group ): void
    {
        ! $option && throw new \Exception( "No slug was given" );
        ! $group  && throw new \Exception( "No options were given" );
        
        $this->options_storage[ $option ] = new Option( $this->gateway, $option, $group );
        $this->options_loaded[]           = $option;
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
    
	public function __get( $name ): ?Option
	{
         if( is_object( $name ) && $this->isClassUseTrait( $name::class, Service::class) ){
            $this->$name;
            
            return null;
        }
        
		if( isset( $this->options_storage[ $name ] ) ){
            return $this->options_storage[ $name ];
        }
        
        $this->loadOption( $name, $this->options_group );
		
		return $this->options_storage[ $name ];
	}
	
	public function __set( $name, $value ): void
	{
        if( is_object( $value ) && $this->isClassUseTrait( $value::class, Service::class) ){
            $this->$name = $value;
            
            return;
        }
        
        if( $value instanceof Option ){
            $this->options_storage[ $name ] = $value;
            
        }elseif( is_object( $value ) || is_array( $value ) ){
            $this->loadOption( $name, $this->options_group );
   
		}else{
			$this->options_storage[ $name ]->setStorage( $value );
		}
	}
	
	public function __isset( $name )
	{
		return isset( $this->options_storage[ $name ] );
	}
    
    /**
     * @param string|null $options_group
     *
     * @return Options
     */
    public function setOptionsGroup( ?string $options_group ): self
    {
        $this->options_group = $options_group;
        
        return $this;
    }
    
    /**
     * @param array $options_to_load
     *
     * @return Options
     */
    public function setOptionsToLoad( array $options_to_load ): self
    {
        $this->options_to_load = $options_to_load;
        
        return $this;
    }
}