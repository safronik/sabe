<?php

namespace Safronik\Services\Request;

// Templates
use Safronik\Core\CodeTemplates\Hydrator;
use Safronik\Core\CodeTemplates\Interfaces\Serviceable;
use Safronik\Core\CodeTemplates\Service;
use Safronik\Core\Variables\Server;

// Applied

class Request implements Serviceable, \Stringable
{
    use Hydrator, Service;
    
    public static string $service_alias = 'request';
    
    private string|self $url;
    
    public string $id;
    public string $path_id;
    
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
        // Set from itself @todo Why do I need that?
        if( $request instanceof static ){
            $this->hydrateFrom( $request );
            
            return;
        }
        
        // Set from URL string
        if( is_string( $request ) ){
            $this->setRequest( $request );
            
            return;
        }
        
        // Default. New request
        $this->setRequest( Server::get( 'REQUEST_SCHEME' ) . '://' . Server::get( 'HTTP_HOST' ) . Server::get( 'REQUEST_URI' ) );
    }
    
    /**
     * @param mixed $url
     */
    public function setRequest( string $url ): void
    {
        $this->url = $url;
        $this->hydrateFrom( parse_url( $this->url ) );
        
        // Get every URL param automatically
        $this->setQuery( $this->query ?? '') ;
        $this->query && parse_str( $this->query, $this->parameters );
        
        // Set additional params
        $this->setHomeUrl(Server::get( 'REQUEST_SCHEME' ) . '://' . Server::get( 'HTTP_HOST' ) . '/');
        $this->setMethod( Server::get( 'REQUEST_METHOD' )) ;
        $this->setPort( $this->port ?? Server::get( 'SERVER_PORT' )) ;
        $this->setRoute( preg_split( '@/@', $this->path, -1, PREG_SPLIT_NO_EMPTY )) ;
        $this->setSsl( $this->scheme === 'https') ;
        
        $this->sanitizeParams( $this->parameters );
        
        $this->setId( md5( $this ) );
        $this->setPathId( md5( $this->url ) );
        
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
    
    public function getParam( string $param_name, string $type = null ): mixed
    {
        $param_raw = $this->parameters[ $param_name ] ?? null;
        
        return $param_raw && $type
            ? settype( $param_raw, $type )
            : $param_raw;
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
    
    public function getId(): string
    {
        return $this->id;
    }
    public function setId( string $id ): void
    {
        $this->id = $id;
    }
    
    public function getPathId(): string
    {
        return $this->path_id;
    }
    public function setPathId( string $path_id ): void
    {
        $this->path_id = $path_id;
    }
    
    public function getMethod(): string
    {
        return $this->method;
    }
    public function setMethod( string $method ): void
    {
        $this->method = $method;
    }
    
    public function getPort(): int
    {
        return $this->port;
    }
    public function setPort( int $port ): void
    {
        $this->port = $port;
    }
    
    public function getRoute(): array
    {
        return $this->route;
    }
    public function setRoute( array $route ): void
    {
        $this->route = $route;
    }
    
    public function isSsl(): bool
    {
        return $this->ssl;
    }
    public function setSsl( bool $ssl ): void
    {
        $this->ssl = $ssl;
    }
    
    public function getQuery(): string
    {
        return $this->query;
    }
    public function setQuery( string $query ): void
    {
        $this->query = $query;
    }
    
    public function getHomeUrl(): string
    {
        return $this->home_url;
    }
    public function setHomeUrl( string $home_url ): void
    {
        $this->home_url = $home_url;
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