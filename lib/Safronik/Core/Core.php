<?php

namespace Safronik\Core;

use Safronik\Core\Variables\Server;
use Safronik\Services\Services;

class Core
{
    public function __construct()
    {
        \Safronik\Services\DB\DB::initialize(
            new \Safronik\Services\DB\Config([
                'driver'    => 'PDO',
                'username'  => Server::get( 'SERVER_NAME' ) === 'sue.loc' ? 'root' : 'cl82418_sue',
                'password'  => Server::get( 'SERVER_NAME' ) === 'sue.loc' ? 'root' : 'qaswEDFR123321',
                'db_prefix' => 'cms_',
            ])
        );

        Services::initialize(
            \Safronik\Services\DB\Gateways\DBGateways::getInstance(),
        );
    }
}