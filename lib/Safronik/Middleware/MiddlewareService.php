<?php

namespace Safronik\Middleware;

use Safronik\Core\Config;

final class MiddlewareService
{
    private const CONFIG_PATH    = 'middlewares';
    private const CONFIG_REQUEST = 'middlewares.config';

    private string $commonKey;

    public function __construct()
    {
        $config = Config::get(self::CONFIG_REQUEST);
        $this->commonKey = $config['common'] ?? 'common';
    }

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
            $this->commonKey,
            self::CONFIG_PATH
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

        return self::CONFIG_PATH . ".$configPath.$method";
    }
}