<?php

namespace Safronik\Middleware;

use Safronik\Core\Config\Config;
use Safronik\Core\Config\Defaults;

final class MiddlewareService
{
    private const CONFIG_PATH        = 'middlewares';
    private const DEFAULT_COMMON_KEY = 'common';

    private string $commonKey;
    private Config $config;

    public function __construct( Config $config )
    {
        $this->config    = $config;
        $this->commonKey = $this->config->get('middlewares.meta.common_key') ?? self::DEFAULT_COMMON_KEY;
    }

    public function executeFor( object $target, string $method, array $parameters = [] ): void
    {
        $middlewaresToRun = $this->getMiddlewaresFromConfig( $target, $method );

        foreach( $middlewaresToRun as $middleware ){

            $middleware instanceof MiddlewareInterface
                || throw new \BadMethodCallException( 'Middleware ' . $middleware::class . ' must implement MiddlewareInterface' );

            $middleware = new $middleware();
            $middleware->execute( $parameters );

        }
    }

    private function getMiddlewaresFromConfig( object $target, string $method ): array
    {
        $configRequest = $this->convertTargetToConfigRequest($target, $method);

        return $this->config->getRegressiveWithKey(
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
        $configPath[count($configPath) - 1] = str_replace( Defaults::service_words->value(), '', $configPath[count($configPath) - 1]);
        $configPath = implode( '.', $configPath);

        return self::CONFIG_PATH . ".$configPath.$method";
    }
}