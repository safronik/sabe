<?php

namespace Safronik\Core\Variables;

class Request extends Variables{
	
	public $http_headers = [];
	
	protected function getVariable( $name ) {
        return $_REQUEST[ $name ] ?? '';
	}
    
    protected function getAllVariablesNames(): array
    {
        return array_keys( $_REQUEST );
    }
    
	public static function getHTTPHeaders()
	{
		$self = self::getInstance();
		if( $self->http_headers ){
			return $self->http_headers;
		}
		
		foreach( $_SERVER as $key => $val ){
			if( 0 === stripos( $key, 'http_' ) ){
				$server_key = preg_replace( '/^http_/i', '', $key );
				$key_parts  = explode( '_', $server_key );
				if( strlen( $server_key ) > 2 ){
					foreach( $key_parts as $part_index => $part ){
						if( $part === '' ){
							continue;
						}
						$key_parts[ $part_index ]    = strtolower( $part );
						$key_parts[ $part_index ][0] = strtoupper( $key_parts[ $part_index ][0] );
					}
					$server_key = implode( '-', $key_parts );
				}
				$self->http_headers[ $server_key ] = $val;
			}
		}
		
		return $self->http_headers;
	}
}