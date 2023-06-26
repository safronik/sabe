<?php

namespace Safronik\Services\Request;

// Templates
use Safronik\Core\CodeTemplates\Hydrator;
use Safronik\Core\CodeTemplates\Service;

// Applied
use Safronik\Services\Serviceable;
use Safronik\Core\Variables\Server;

class Request implements Serviceable, \Stringable
{
    use Hydrator, Service;
    
    public static string $service_alias = 'request';
    
    private string|self $request;
    
    public string $id;
    
    public string $method;
    public string $scheme;
    public string $host;
    public int    $port;
    public string $user;
    public string $pass;
    public string $path;
    public string $query;
    public string $fragment;
    
    public array  $route = [];
    public bool   $ssl = false;
    public array  $parameters = [];
    public string $home_url;
    
    public function __construct( Request|string|null $request  = null )
    {
        $this->request = $request ?? Server::get( 'REQUEST_SCHEME' ) . '://' . Server::get( 'HTTP_HOST' ) . Server::get( 'REQUEST_URI' );
        $this->setRequest( $this->request );
    }
    
    /**
     * @param mixed $request
     */
    public function setRequest( Request|string $request ): void
    {
        if( ! $request instanceof static ){
            
            $this->hydrateFrom( parse_url( $this->request ) );
            
            $this->home_url = Server::get( 'REQUEST_SCHEME' ) . '://' . Server::get( 'HTTP_HOST' ) . '/';
            $this->method   = Server::get( 'REQUEST_METHOD' );
            $this->port     = $this->port ?? Server::get( 'SERVER_PORT' );
            $this->route    = preg_split( '@/@', $this->path, -1, PREG_SPLIT_NO_EMPTY );
            $this->ssl      = $this->scheme === 'https';
            $this->query    = '';
            
            parse_str( $this->query, $this->parameters );
            
            $this->sanitizeParams( $this->parameters );
            
        }else{
            $this->hydrateFrom( $request );
        }
        
        $this->id = md5( $this );
    }
    
    public function shiftRoute()
    {
        array_shift( $this->route );
        
        return $this;
    }
    
    public function currentRoute(): string
    {
        return current( $this->route );
    }
    
    public function setParams( ...$params ){
        foreach( $params as $name => $value ){
            $this->parameters[ $name ] = $value;
        }
    }
    
    public function removeParams( ...$params_names )
    {
        foreach( $params_names as $name ){
            unset( $this->parameters[ $name ] );
        }
    }
    
    private function sanitizeParams( $params ): array
    {
        foreach( $params as &$param ){
            $param = is_array( $param )
                ? $this->sanitizeParams( $param )
                : $this->sanitizeParam( $param );
        }
        
        return $params;
    }
    
    private function sanitizeParam( $param ): string
    {
        return preg_replace( '/[^\w.-_]/', '', $param );
    }
    
    
    // Stringable
    public function __toString(): string
    {
        return $this->toString( $this );
    }
    
    private function toString( $props = null ): string
    {
        $out = '';
        
        if( $props && is_object( $props ) || is_iterable( $props ) ){
            foreach( $props as $prop ){
                $out .= $this->toString( $prop );
            }
        }else{
            $out = (string) $props;
        }
        
        return $out;
    }
}