<?php

namespace Safronik\Services\Visitor;

use Safronik\Core\CodeTemplates\Singleton;
use Safronik\Core\Variables\Server;
use Safronik\Core\Variables\Request;

class IP{
	
	use Singleton;
	
	private $ip;
	
	public static function get()
	{
		$self = IP::getInstance();
		if( $self->ip ){
			return $self->ip;
		}
		
		// X-Forwarded-For
        $headers = Request::getHTTPHeaders();
        if( isset( $headers['X-Forwarded-For'] ) ){
	        $tmp = explode( ',', trim( $headers['X-Forwarded-For'] ) );
	        $self->ip = trim( $tmp[0] );
        }
		$self->ip = $self->validate( $self->ip ) ? $self->ip : null;
		
		// Remote address
		if( ! $self->ip ){
            $self->ip = Server::get( 'REMOTE_ADDR' );
		}
		$self->ip = $self->validate( $self->ip ) ? $self->ip : null;
		
		return $self->ip ?: '0.0.0.0';
	}
	
	private function validate( $ip )
	{
		if( ! $ip ){
			return false;
		}
		
		if( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ){
			return 'v4';
		}
		
		return false;
	}
}