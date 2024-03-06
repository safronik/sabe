<?php

namespace Safronik\Services\DB\Gateways;

interface GatewayInterface
{
    public function setAppPrefixForDB( string $prefix ): string;
}