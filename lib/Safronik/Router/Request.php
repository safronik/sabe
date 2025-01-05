<?php

namespace Safronik\Router;

// Templates
use Safronik\CodePatterns\Generative\Singleton;
use Safronik\CodePatterns\Structural\Hydrator;
use Safronik\Globals\Server;

// Exceptions
use Safronik\Router\Exceptions\CliRequestException;
use Safronik\Router\Exceptions\RequestException;

// Applied

class Request implements \Stringable
{
    use Hydrator, Singleton;

    // ID
    public string $id;
    public string $path_id;

    // URL PARTS
    private string $url;
    public  string $method;
    public  string $scheme;
    public  string $host;
    public  int    $port;
    public  string $user;
    public  string $pass;
    public  string $path;
    public  string $query;
    public  string $fragment;

    // Additional parameters
    private string $type;
    public  array  $route = [];
    public  bool   $ssl = false;
    public  array  $parameters = [];
    public  mixed  $body = [];
    public string  $home_url;

    /**
     * @throws RequestException
     */
    public function __construct( Request|array|string|null $request  = null )
    {
        // Set from itself or params @todo Do I need this?
        if( $request instanceof static || is_array( $request ) ){
            $this->hydrate( $request );
            
            return;
        }
        
        // Default. New request
        $this->type = self::determineType();
        $this->setRequest( $this->type );
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
        $url_path       = parse_url(
            Server::get( 'REQUEST_SCHEME' ) . '://' . Server::get( 'HTTP_HOST' ) . Server::get( 'REQUEST_URI' ),
            PHP_URL_PATH
        );

        return match(true){
            empty( $request_scheme )    => 'cli',
            str_starts_with( $url_path, '/api/rest' ) => 'rest',
            str_starts_with( $url_path, '/api'      ) => 'api',
            $request_scheme === 'https' => 'web',
            $request_scheme === 'http'  => 'web',
            $request_scheme === 'ssh'   => 'cli',
            $request_scheme === 'ftp'   => 'ftp',
        };
    }


    /**
     * @param mixed $request_type
     * @throws RequestException
     */
    private function setRequest( string $request_type ): void
    {
        switch( $this->type ){
            case 'cli':  $this->setCliRequest(); break;
            case 'web':  $this->setWebRequest(); break;
            case 'ftp':  $this->setFtpRequest(); break;
            case 'api':  $this->setApiRequest(); break;
            case 'rest': $this->setRestRequest(); break;
        }
    }

    /**
     * @throws CliRequestException
     */
    private function setCliRequest(): void
    {
        $path             = str_replace( [ '.', '/', ], '\\', $_SERVER['argv'][1] ?? null );
        $path   || throw new CliRequestException("Path not specified");

        $method           = $_SERVER['argv'][2] ?? null;
        $method || throw new CliRequestException("Method not specified");

        $input_parameters = array_slice( $_SERVER['argv'] ?? [], 3, null, true );
        $values           = array_filter( $input_parameters, static function( $key ){ return $key % 2 === 0; }, ARRAY_FILTER_USE_KEY );
        $names            = array_filter( $input_parameters, static function( $key ){ return $key % 2 === 1; }, ARRAY_FILTER_USE_KEY );
        array_walk( $names, static function( &$name ){ $name = trim( $name, '-' ); } );

        count( $names ) === count( $values ) || throw new CliRequestException("Parameters keys and values count doesn't match");

        $parameters = array_combine( $names, $values );


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
        
        // Get every URL param automatically
        $this->query = $this->query ?? '';
        $this->query && parse_str( $this->query, $this->parameters );
        $this->setBody();

        // @todo use sanitizer
        $this->sanitizeParams( $this->parameters );

        // Set additional params
        $this->home_url = Server::get( 'REQUEST_SCHEME' ) . '://' . Server::get( 'HTTP_HOST' ) . '/';
        $this->method   = Server::get( 'REQUEST_METHOD' );
        $this->port     = $this->port ?? Server::get( 'SERVER_PORT' );
        $this->route    = preg_split( '@/@', $this->path, -1, PREG_SPLIT_NO_EMPTY );
        $this->ssl      = $this->scheme === 'https';
        $this->id       = md5( $this );
        $this->path_id  = md5( $this->url );
    }
    
    private function setApiRequest(): void
    {
        $uri = preg_replace( '@^/api@', '', Server::get( 'REQUEST_URI' ));
        $this->setWebRequest( Server::get( 'REQUEST_SCHEME' ) . '://' . Server::get( 'HTTP_HOST' ) . Server::get( 'REQUEST_URI' ) );
        // Additional set up
    }

    private function setRestRequest(): void
    {
        $this->setWebRequest( Server::get( 'REQUEST_SCHEME' ) . '://' . Server::get( 'HTTP_HOST' ) . Server::get( 'REQUEST_URI' ) );
        // Additional set up
    }

    private function setFtpRequest(): void
    {
        // @todo implement
    }

    public function getParam( string $param_name, string $type = null ): mixed
    {
        $param_raw = $this->parameters[ $param_name ] ?? null;
        
        return $param_raw && $type
            ? settype( $param_raw, $type )
            : $param_raw;
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

    public function __toString(): string
    {
        return $this->toString( $this );
    }
    
    private function toString( mixed $props ): string
    {
        return ! is_scalar( $props )
            ? array_reduce( (array) $props, function( $tmp, $prop ){ $tmp .= $this->toString( $prop ); return $tmp; }, '' )
            : (string) $props;
    }
    
    public function getType(): string
    {
        return $this->type;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    private function setBody(): void
    {
        if( ! empty( $_POST ) ){
            $this->body = &$_POST;
            return;
        }

        $post = file_get_contents( 'php://input' );
        $post = $post
            ? json_decode( $post, true, 512, JSON_THROW_ON_ERROR )
            : [];
        
        $this->body = json_last_error() === JSON_ERROR_NONE
            ? $post
            : [];
    }
}