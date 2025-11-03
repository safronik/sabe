<?php

namespace Safronik\Core\Structure;

use Safronik\Core\Apps;
use Safronik\Core\Core;

trait ApplicationAccess
{
    readonly protected ?string $appNamespace;
    readonly protected ?string $appName;
    readonly protected ?Core   $app;

    /**
     * Gets current app namespace of static class
     *
     * @return string
     */
    public function getCurrentAppNamespace(): string
    {
        // Cache the result
        if( ! isset( $this->appNamespace ) ) {
            [ $appNamespace ] = explode( '\\', static::class );
            $this->appNamespace = $appNamespace;
        }

        return $this->appNamespace;
    }

    /**
     * Gets current app name
     *
     * @return string
     */
    public function getCurrentAppName(): string
    {
        return $this->appName
            ?? $this->appName = strtolower( $this->getCurrentAppNamespace() );
    }

    /**
     * @throws \Exception
     */
    public function app(): Core
    {
        return $this->app
            ?? $this->app = Apps::get( $this->getCurrentAppName() );
    }
}