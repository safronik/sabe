<?php

namespace Safronik\Middleware;

use Safronik\Core\Config;

final class MiddlewareService
{
    public function executeFor( object $target, string $method, array $parameters = [] ): void
    {
        $middlewaresToRun = $this->getMiddlewaresFromConfig( $target, $method );

        foreach( $middlewaresToRun as $middleware ){

            $middleware = new $middleware();
            $middleware->execute( $parameters );

        }
    }

    private function getMiddlewaresFromConfig( object $target, string $method ): array
    {
        $configRequest = $this->convertTargetToConfigRequest($target, $method);

        return Config::getRegressiveWithKey(
            $configRequest,
            'common',
            'middlewares'
        );
    }

    private function convertTargetToConfigRequest( object $target, string $method ): string
    {
        $method     = str_replace( [ 'action', 'method' ], '', $method);
        $method     = strtolower( $method);

        $configPath = str_replace( '\\', '.', $target::class);
        $configPath = strtolower( $configPath);
        $configPath = explode( '.', $configPath);
        $configPath[count($configPath) - 1] = str_replace( Config::get('service_words'), '', $configPath[count($configPath) - 1]);
        $configPath = implode( '.', $configPath);

        return "middlewares.$configPath.$method";
    }
}