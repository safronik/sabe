<?php

namespace Safronik\Core\CodeTemplates;

trait Service
{
    public static function getAlias(): string|null
    {
        return static::$service_alias ?? null;
    }
    
    public static function getGatewayAlias(): string|null
    {
        return static::$gateway_alias ?? null;
    }
    
    protected function initService( $gateway )
    {
    
    }
}