<?php

namespace Safronik\Core\Data;

interface Storageable extends \IteratorAggregate, \ArrayAccess, \Serializable, \Countable
{
    //protected function assignProperties( $properties );
    //private function assignProperty( $name, $value );
    //private function createComplexProperty( $value = array() );
    //private function createSimpleProperty( $value );
    //private function createPropertyIfNotExists( $name );
    
    public function convertToStorage( $data );
    public function getArrayFromStorage( $storage = null );
    public function serializeStorage();
    public function getStorage();
    public function isEmpty();
    public function isScalar();
    public function getScalarValue();
    public function truncate();
}