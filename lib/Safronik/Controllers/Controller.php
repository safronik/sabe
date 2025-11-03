<?php

namespace Safronik\Controllers;

use Safronik\Controllers\Exceptions\EndpointNotImplementedException;
use Exception;
use Safronik\Core\Structure\Element;
use Safronik\Router\Endpoint;
use Safronik\Router\Request;
use Safronik\Router\Routes\Route;
use Safronik\Views\ViewInterface;

abstract class Controller extends Element{

    protected Route         $route;
    protected Request       $request;
    protected ViewInterface $view;

    public function __construct( Route $route )
    {
        $this->request    = Request::getInstance();
        $this->route      = $route;

        // Initialize
        method_exists($this, 'init' ) && $this->init();
    }

    protected function actionShowEndpoints(): void
    {
        $this->view->renderData( ['endpoints' => $this->getEndpoints() ] );
    }

    protected function getEndpoints( ?callable $filter = null ): array
    {
        // Set default filter
        $filter = $filter
            ?? static fn($method) =>
                str_starts_with( $method->getName(), 'method' ) ||
                str_starts_with( $method->getName(), 'action' );

        // Get and filter
        $controller_methods_reflections = ( new \ReflectionClass( static::class ) )->getMethods();
        $controller_methods_reflections = array_filter( $controller_methods_reflections, $filter );

        // Filter magic methods
        $controller_methods_reflections = array_filter(
            $controller_methods_reflections,
            static fn( $method ) => ! str_starts_with( $method->getName(), '__' )
        );

        // Gather
        foreach( $controller_methods_reflections as $controller_methods_reflection ){
            $endpoint = new Endpoint($controller_methods_reflection, strtolower( implode( '/', $this->route->getRoute() ) ) );
            $endpoints[ $endpoint->getName() ] = $endpoint;
        }

        return $endpoints ?? [];
    }

    public function executeEndpoint( string $endpoint_name ): void
    {
        // Run middlewares
        $this->runMiddlewares( $endpoint_name, [ 'request' => $this->request ] );

        // Execute endpoint
        try{
            $this->call($endpoint_name );
        }catch( \BadMethodCallException ){
            throw new EndpointNotImplementedException(
                "Trying to call not implemented {$this->route->getPath()}",
                501,
            );
        }
    }

    /**
     * @param Exception $exception
     * @return void
     */
    public function handleError( Exception $exception ): void
    {
        $this->view->renderError( $exception );
    }
}