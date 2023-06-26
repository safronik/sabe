<?php

namespace Safronik\Services\Options;

use Safronik\Core\Data\Storage;
use Safronik\Core\Data\Helper;

abstract class OptionAbstract extends Storage implements \Stringable{
 
	/**
	 * Option name without prefix
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * Settings prefix
	 *
	 * @var string
	 */
	protected $group;
    
    ///**
    // * @var Storage|mixed
    // */
    //protected mixed $storage;
    
    /**
     * Returns defaults value of an option
     *
     * @return mixed
     */
    abstract protected function getDefaults(): mixed;
    
	/**
	 * @param $option string
	 * @param $group  string
	 *
	 * @return array|null
	 */
	abstract protected function load( string $option, string $group );
    
    /**
	 * Save option to the DB or other storage
	 *
	 * @return void
	 */
	abstract public function save();
 
	/**
	 * Permanent delete the current option from the storage
	 *
	 * @return bool
	 */
	abstract public function remove(): bool;
 
	public function __construct( $name, $group = '' )
	{
        $this->name  = $name;
        $this->group = $group;
        
        $loaded_options  = $this->load( $name, $group );
        $default_options = $this->getDefaults();
        $merged_options  = $this->mergeLoadedWithDefaults( $loaded_options, $default_options );
        if( $this->shouldUseStorage( $merged_options ) ){
            parent::__construct($merged_options, true);
        }else{
            $this->storage = $merged_options;
        }
	}
 
    private function shouldUseStorage( $data ): bool
    {
        return ! is_scalar( $data ) || ( is_string( $data ) && Helper::unpackIfJSON( $data ) );
    }
    
	/**
	 * Cast the input variable to certain type given by an example
	 *
	 * @param $input
	 * @param $example
	 *
	 * @return mixed
	 */
	public function castType( $input, $example ): mixed
	{
		settype( $input, gettype( $example ) );
		
		return $input;
	}
    
    /**
     * Return an array with merged options
     *
     * @param array $loaded_options
     * @param array $default_options
     *
     * @return array|int|string
     */
	private function mergeLoadedWithDefaults( mixed $loaded_options, mixed $default_options ): mixed
	{
		// No default values for this option
		if( empty( $default_options ) ){
			return $loaded_options;
		}
		
        if( $loaded_options instanceof \Iterable && $default_options instanceof \Iterable ){
		    $merged = [];
            foreach( $default_options as $name => $default_value ){
                $merged[ $name ] = isset( $loaded_options[ $name ] )
                    ? $this->castType( $loaded_options[ $name ], $default_value )
                    : $default_value;
            }
        }else{
            $merged = $this->castType( $loaded_options, $default_options );
        }
        
        return $merged;
	}
    
    //public function __isset( string $name ): bool
    //{
    //    return isset( $this->storage[ $name ] );
    //}
    //
    //public function __get( string $name )
    //{
    //    return $this->storage[ $name ] ?? null;
    //}
    //
    //public function __set( string $name, $value ): void
    //{
    //    $this->storage[ $name ] = is_scalar( $this->storage[ $name ] )
    //        ? $this->storage[ $name ]
    //        : new Storage( $value );
    //}
    
    public function __toString(): string
    {
        return is_scalar( $this->storage )
            ? $this->storage
            : 'Storage';
    }
    
    public function toInt(): int
    {
        return is_scalar( $this->storage )
            ? (int) $this->storage
            : 0;
    }
    
    /**
     * @param Storage|mixed $storage
     */
    public function setStorage( mixed $storage ): void
    {
        $this->storage = $storage;
    }
}