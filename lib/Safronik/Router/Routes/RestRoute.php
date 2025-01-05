<?php

namespace Safronik\Router\Routes;

use Safronik\Router\Exceptions\RouterException;
use Safronik\Router\Request;

final class RestRoute extends AbstractRoute
{
    public function __construct( string $type, string $path, string $root_namespace, string $method )
    {
        parent::__construct( $type, $path, $root_namespace );

        $this->controller   = $this->namespace . '\\' . implode( '\\', $this->route ) . 'Controller';
        $this->endpoint     = ucfirst( strtolower( $method ) );
        $this->endpointType = 'method';
    }

    /**
     * @throws RouterException
     */
    public function searchForAvailableEndpoint(): void
    {
        try{
            $this->ensureIsAvailable();
        }catch( RouterException $exception ){
            $this->reduceRoute()
                ->setEndpointType('action')
                ->ensureIsAvailable();
        }
    }

    protected function isEndpointAvailable(): bool
    {
        return true;
    }
}