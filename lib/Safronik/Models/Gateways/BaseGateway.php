<?php

namespace Safronik\Models\Gateways;

use Safronik\DB\DB;

abstract class BaseGateway
{
    protected DB $db;
    
    public function __construct( DB $db )
    {
        $this->db = $db;
    }
}