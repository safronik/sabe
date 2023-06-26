<?php

namespace Safronik\Core\Data;

/*
 * Provides an easy-to-use interface for reading/writing associative array based information
 * by exposing properties which represent each key of the array
*/

class Storage implements Storageable
{
    protected mixed $storage = [];
    
    /**
     * @var bool
     */
    private bool $extract_JSON;
    
    public function __construct( mixed $properties = [], $extract_JSON = false )
    {
        $this->extract_JSON = $extract_JSON;
        $this->assignProperties( $properties );
    }
    
    private function assignProperties( $properties ): void
    {
        if( is_string($properties) && $this->extract_JSON ){
            $properties = Helper::unpackIfJSON( $properties ) ?: $properties;
            if( is_scalar($properties) ){
                $this->assignProperty(0, $properties);
                
                return;
            }
        }
        
        foreach( $properties as $name => $value ){
            $this->assignProperty( $name, $value );
        }
    }
    
    private function assignProperty( $name, $value ): void
    {
        if( $this->extract_JSON ){
            $value = Helper::unpackIfJSON( $value ) ?: $value;
            //var_dump( $value);
        }
        $this->storage[ $name ] = is_array( $value )
            ? $this->createComplexProperty( $value )
            : $this->createSimpleProperty( $value );
    }
	
    private function createComplexProperty( $value = array() )
    {
        return new self( $value, $this->extract_JSON );
    }
    
    private function createSimpleProperty( $value )
    {
        return $value;
    }
    
    private function createPropertyIfNotExists( $name )
    {
        if( isset( $this->$name ) ){
            return false;
        }
        $this->assignProperty( $name, [] );
        
        return true;
    }
	
    public function convertToStorage( $data, $extract_json = false )
    {
        foreach( $data as $name => &$value ){
            if( $this->extract_JSON ){
                $value = Helper::unpackIfJSON( $value ) ?: $value;
            }
            if( is_array( $value ) ){
                $value = new self( $value, $extract_json );
            }
        }
    }

    
	public function truncate()
	{
		return $this->storage = null;
	}
    
    public function getArrayFromStorage( $storage = null )
    {
		$storage = $storage ?: $this->storage;
		
		$tmp = [];
        foreach( $storage as $name => $item ){
            $tmp[ $name ] = $item instanceof self
                ? $this->getArrayFromStorage( $item )
                : $item;
        }
		
		return $tmp;
    }
	
    /*** ArrayAccess methods **/
    public function offsetExists( $offset )
    {
        return isset( $this->$offset );
    }
    
    public function offsetGet( $offset )
    {
        return $this->$offset;
    }
    
    public function offsetSet( $offset, $value )
    {
        $this->$offset = $value;
    }
    
    public function offsetUnset( $offset )
    {
        unset( $this->$offset );
    }
    
    /*** IteratorAggregate methods **/
    public function getIterator()
    {
        return new \ArrayIterator($this->storage);
    }
    
    /*** Serialize methods **/
    public function unserialize( $data )
    {
        return new self( $data );
    }
    
    public function serialize()
    {
        return $this->serializeStorage( $this->storage );
    }
    
    public function serializeStorage()
    {
        $serialized = [];
        foreach($this->storage as $name => $item){
            if( $item instanceof self ){
                $serialized[$name] = $item->getStorage();
            }else{
                $serialized[$name] = $item;
            }
        }
        
        return json_encode( $serialized );
    }
    
    /*** Countable methods **/
    public function count()
    {
        return count($this->storage);
    }
    
    /*** getters and setters methods **/
    public function getStorage()
    {
        return $this->storage;
    }
    
    public function isEmpty()
    {
        return count( $this->storage ) === 0;
    }
    
    public function isScalar()
    {
        return count($this) && $this->{0};
    }
    
    public function getScalarValue()
    {
        return $this->{0};
    }
    
    /*** Magic methods **/
    public function __isset( $name )
    {
        return isset( $this->storage[ $name ] );
    }
    
    public function __get( $name )
    {
        $this->createPropertyIfNotExists( $name );
        
        return $this->storage[ $name ];
    }
    
    public function __set( $name, $value )
    {
        $this->assignProperty( $name, $value );
    }
    
    
    public function __unset( $name )
    {
        unset( $this->storage[ $name ] );
    }
    
    public function __debugInfo()
    {
        return $this->storage;
    }
}