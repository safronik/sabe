<?php

namespace Safronik\Services\User;

interface DBUserGatewayInterface
{
    public function getUserBy( $type, $needle ): array;
}