<?php

namespace Safronik\Core;

use Safronik\Services\Services;

/**
 * Class Core
 
 * @package Safronik\Core
 */
class Core
{
    /**
     * Core constructor.
     */
    public function __construct()
    {
        $this->initializeDB();
        $this->initializeServices();
        
    }
    
    /**
     * Initialize DB
     */
    private function initializeDB()
    {
        \Safronik\Services\DB\DB::initialize(
            new \Safronik\Services\DB\DBConfig([
                'database' => 'sabe',
                'username' => 'root',
                'password' => 'root',
            ])
        );
    }
    
    /**
     * Initialize services
     */
    private function initializeServices()
    {
        Services::initialize(
            \Safronik\Services\DB\Gateways\DBGateways::getInstance(),
        );
    }
}