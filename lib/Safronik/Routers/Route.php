<?php

namespace Safronik\Routers;

class Route
{
    public string $id;
    
    private string $type;   // Controller type (web, api, ftp, cli, ... )
    private string $path;   // Controller to call
    private string $method; // Method to call in controller
    private array  $route;
    
    public function __construct( string $type, string $method, string $path )
    {
        $this->type       = strtolower($type ); // cli, web, api, ftp
        $this->method     = ucfirst( strtolower( $method ) );
        $this->path       = $this->standardizePath( $path, $this->type );
        $this->route      = explode( '\\', $this->path );
    }
    
    private function standardizePath( string $path, string $type ): string
    {
        $path = str_replace( '/', '\\', $path );
        $path = trim( $path, '\\' );
        $path = ucwords( $path, '\\' );
        $path = $type . '\\' . $path;
        
        return $path;
    }
    
    /**
     * Reducing the route
     *
     * api/controller/action::get() became api/controller::action()
     *
     * @return void
     */
    public function reduceRoute(): void
    {
        $this->method = array_pop( $this->route );
    }
    
    public function getPath(): string
    {
        return implode( '\\', $this->route );
    }
    
    public function getType(): string
    {
        return $this->type;
    }
    
    public function getMethod(): string
    {
        return $this->method;
    }
    
    public function getRoute(): array
    {
        return $this->route;
    }
    
    public function setMethod( string $method ): void
    {
        $this->method = $method;
    }
}