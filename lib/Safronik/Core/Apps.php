<?php

namespace Safronik\Core;

class Apps
{
    private static array $apps = [];

    public static function init( string $appName, array $additionalConfig = [] ): void
    {
        isset( static::$apps[ $appName ] )
            && throw new \Exception( "App $appName is already initialized" );

        static::$apps[ $appName ] = new Core( $appName, $additionalConfig );
    }

    public static function get( string $appName ): Core
    {
        isset( static::$apps[ $appName ] )
            || throw new \Exception( "App $appName is not initialized" );

        return static::$apps[ $appName ];
    }
}