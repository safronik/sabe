<?php

namespace Safronik\Core\Structure;

use Safronik\Middleware\MiddlewareService;

trait Middlewares
{
    private ?MiddlewareService $middlewareService = null;

    public function runMiddlewares( string $method, array $parameters = [] ): void
    {
        if( ! $this->middlewareService ){
            $this->middlewareService = new MiddlewareService( $this->app()->config );
        }

        $this->middlewareService->executeFor(
            $this,
            $method,
            $parameters
        );
    }
}