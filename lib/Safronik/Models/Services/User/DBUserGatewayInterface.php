<?php

namespace Safronik\Models\Services\User;

interface DBUserGatewayInterface
{
    public function getUserBy( $type, $needle ): array;
}