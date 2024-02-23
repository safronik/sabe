<?php

namespace Safronik\Services\DB\Gateways;

use Safronik\Services\DB\DB;

abstract class AbstractDBGateway implements  GatewayInterface
{
    protected DB $db;
    
    public function __construct( DB $db )
    {
        $this->db = $db;
    }
}