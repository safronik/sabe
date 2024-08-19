<?php

namespace Safronik\Routers;

// Templates
use Safronik\CodePatterns\Generative\Singleton;
use Safronik\CodePatterns\Structural\Hydrator;
use Safronik\Globals\Server;

// Exceptions
use Safronik\Routers\Exceptions\RequestException;

// Applied

class Request implements \Stringable
{
    use Hydrator, Singleton;
    
    private string|self $url;
    
    public string $id;
    public string $path_id;
    private string $type;
    
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
    public mixed  $body = [];
    public string $home_url;
    
    public function __construct( Request|array|string|null $request  = null )
    {
        // Set from itself or params @todo Do I need this?
        if( $request instanceof static || is_array( $request ) ){
            $this->hydrate( $request );
            
            return;
        }
        
        // Default. New request
        $this->setRequest( self::determineType() );
    }
    
    /**
     * @param mixed $request_type
     */
    public function setRequest( string $request_type ): void
    {
        $this->setType( $request_type );
        
        switch( $this->type ){
            case 'cli': $this->setCliRequest(); break;
            case 'web': $this->setWebRequest(); break;
            case 'ftp': $this->setFtpRequest(); break;
            case 'api': $this->setApiRequest(); break;
        }
    }
    
    private function setCliRequest(): void
    {
        $path             = str_replace( [ '.', '/', ], '\\', $_SERVER['argv'][1] ?? null );
        $method           = $_SERVER['argv'][2] ?? null;
        $input_parameters = array_slice( $_SERVER['argv'] ?? [], 3, null, true );
        $values           = array_filter( $input_parameters, static function( $key ){ return $key % 2 === 0; }, ARRAY_FILTER_USE_KEY );
        $names            = array_filter( $input_parameters, static function( $key ){ return $key % 2 === 1; }, ARRAY_FILTER_USE_KEY );
        array_walk( $names, static function( &$name ){ $name = trim( $name, '-' ); } );
        
        count( $names ) === count( $values )
            || throw new RequestException("Parameters keys and values count doesn't match");
        
        $parameters = array_combine( $names, $values );
        
        $path   || throw new RequestException("Path not specified");
        $method || throw new RequestException("Method not specified");
        
        $this->hydrate( [
            'method'     => $method,
            'host'       => $_SERVER['PWD'],
            // 'user'       => $_SERVER['USERNAME'],
            'path'       => $path,
            'query'      => implode( ' ', $_SERVER['argv'] ),
            'parameters' => $parameters,
        ] );
    }
    
    private function setWebRequest( $url = null ): void
    {
        $this->url = $url ?? Server::get( 'REQUEST_SCHEME' ) . '://' . Server::get( 'HTTP_HOST' ) . Server::get( 'REQUEST_URI' );
        
        $this->hydrate( parse_url( $this->url ) );
        $this->setType( self::determineType() );
        
        // Get every URL param automatically
        $this->setQuery( $this->query ?? '') ;
        $this->query && parse_str( $this->query, $this->parameters );
        $this->setBody();
        
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
    
    private function setApiRequest(): void
    {
        $uri = preg_replace( '@^/api@', '', Server::get( 'REQUEST_URI' ));
        $this->setWebRequest( Server::get( 'REQUEST_SCHEME' ) . '://' . Server::get( 'HTTP_HOST' ) . $uri );
    }
    
    private function setFtpRequest(): void
    {
        // @todo implement
    }
    
    /**
     * Determines the type of the request (CLI, HTTP, FTP, API, ... ). Could use any condition.<br>
     * Request type forces app to use certain namespace for controllers. @todo is this appropriate?
     *
     * @return string
     */
    public static function determineType(): string
    {
        $request_scheme = $_SERVER['REQUEST_SCHEME'] ?? null;
        $request_scheme = strtolower( $request_scheme );
        
        return match(true){
            empty( $request_scheme )    => 'cli',
            str_starts_with(
                parse_url(
                    Server::get( 'REQUEST_SCHEME' ) . '://' . Server::get( 'HTTP_HOST' ) . Server::get( 'REQUEST_URI' ),
                    PHP_URL_PATH
                ), '/api') => 'api',
            $request_scheme === 'https' => 'web',
            $request_scheme === 'http'  => 'web',
            // $request_scheme === 'ssh'   => 'cli', // @todo
            // $request_scheme === 'ftp'   => 'ftp', // @todo
        };
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
    
    public function getType(): string
    {
        return $this->type;
    }
    
    public function setType( string $type ): void
    {
        $this->type = $type;
    }
    
    public function getPath(): string
    {
        return $this->path;
    }
    
    public function setPath( string $path ): void
    {
        $this->path = $path;
    }
    
    public function getBody(): mixed
    {
        return $this->body;
    }
    
    private function setBody(): void
    {
        if( ! empty( $_POST ) ){
            $this->body = &$_POST;
            return;
        }
        
        $post = json_decode( file_get_contents( 'php://input' ), true );
        
        $this->body = json_last_error() == JSON_ERROR_NONE
            ? $post
            : [];
    }
}