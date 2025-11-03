<?php

namespace Safronik\Router\Routes;

use Safronik\Router\Exceptions\RouterException;
use Safronik\Router\Request;

abstract class Route
{
    protected string $type;     // Controller type (web, api, ftp, cli, ... )
    protected string $path;     // Controller to call
    protected string $controller;
    protected string $endpoint; // Endpoint to call in controller
    protected string $endpointType = 'action';
    protected array  $route;
    protected string $namespace;

    /**
     * @throws RouterException
     */
    abstract public function searchForAvailableEndpoint(): void;

    public function __construct( string $type, string $path, string $root_namespace )
    {
        $this->type       = strtolower( $type ); // cli, web, api, ftp
        $this->namespace  = $root_namespace;
        $this->path       = $this->standardizePath( $path, $this->type );
        $this->route      = explode( '\\', $this->path );
        $this->controller = $this->namespace . '\\' . 'Controllers' . '\\' . implode( '\\', array_slice( $this->route, 0, -1 ) ) . 'Controller';
        $this->endpoint   = ucfirst( $this->route[ array_key_last( $this->route ) ] );
    }

    public static function fabricRoute( string $type, string $path, string $root_namespace, string $method ): static
    {
        return match ($type){
            'cli'  => new CliRoute( $type, $path, $root_namespace ),
            'web'  => new WebRoute( $type, $path, $root_namespace ),
            'ftp'  => new FtpRoute( $type, $path, $root_namespace ),
            'api'  => new ApiRoute( $type, $path, $root_namespace ),
            'rest' => new RestRoute( $type, $path, $root_namespace, $method ),
        };
    }

    protected function standardizePath( string $path ): string
    {
        $path = str_replace( '/', '\\', $path );
        $path = trim( $path, '\\' );

        return ucwords( $path, '\\' );
    }

    /**
     * @throws RouterException
     */
    public function ensureIsAvailable(): void
    {
        $this->isControllerAvailable()
            || throw new RouterException('No controller available', 501 );

        $this->isEndpointAvailable()
            ||  throw new RouterException('No endpoint available', 501 );
    }

    protected function isControllerAvailable(): bool
    {
        return class_exists( $this->controller );
    }

    protected function isEndpointAvailable(): bool
    {
        return method_exists( $this->controller, $this->endpointType . $this->endpoint );
    }

    /**
     * Reducing the route
     *
     * api/controller/action::get() became api/controller::action()
     *
     * @return Route
     */
    protected function reduceRoute(): self
    {
        $this->endpoint   = array_pop( $this->route );
        $this->path       = implode( '\\', $this->route );
        $this->controller = $this->namespace . '\\' . 'Controllers' . '\\' . $this->path . 'Controller';

        return $this;
    }
    
    protected function setEndpoint( string $endpoint ): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    protected function setEndpointType( string $endpointType ): self
    {
        $this->endpointType = $endpointType;

        return $this;
    }

    public function setDefault(): self
    {
        return new static(
            $this->getType(),
            'Default',
            $this->namespace,
            Request::getInstance()->method,
        );
    }

    public function getPath(): string
    {
        return implode( '\\', $this->route );
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEndpoint(): string
    {
        return $this->endpointType . $this->endpoint;
    }

    public function getRoute(): array
    {
        return $this->route;
    }

    public function getController(): string
    {
        return $this->controller;
    }
}