<?php

namespace Safronik\Services\DB\Gateways;

use Safronik\Services\DB\DB;

abstract class AbstractDBGateway implements  GatewayInterface
{
    protected DB $db;
    
    public function __construct( DB $db, $prefix = null )
    {
        $this->db = $db;
        $prefix &&
            $this->setAppPrefixForDB( $prefix );
    }
    
    /**
     * @param string $prefix
     *
     * @return string
     */
    public function setAppPrefixForDB( string $prefix ): string
    {
        $this->db->setAppPrefix( $prefix );
        
        return $this->db->getAppPrefix();
    }
}