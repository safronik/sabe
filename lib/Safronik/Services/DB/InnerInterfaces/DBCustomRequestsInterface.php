<?php

namespace Safronik\Services\DB\InnerInterfaces;

interface DBCustomRequestsInterface
{
	public function prepare( $query, $values );
	public function query( $query = null );
    public function preparePlaceholders( $values = array() );
    //public function preparePlaceholder( $value, $type = 'string', $name = null );
    public function setResponseMode( $response_mode );
    public function fetchAll( $query = null, $response_mode = null, $fetch_argument = null );
    public function fetch( $response_mode = null );
}