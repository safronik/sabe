<?php

namespace Safronik\Services\DB\Gateways;

use Safronik\Services\DB\InnerInterfaces\DBSimpleAccess;
use Safronik\Services\DB\InnerInterfaces\DBHelperMethodsInterface;

abstract class AbstractDBGateway implements  GatewayInterface
{
    protected DBSimpleAccess $db;
    
    public function __construct( DBSimpleAccess|DBHelperMethodsInterface $db )
    {
        $this->db = $db;
    }
}