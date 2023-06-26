<?php

namespace Safronik\Services\User;

// Templates
use Safronik\Core\CodeTemplates\Singleton;

// Applied
use Safronik\Core\Variables\Cookie;

class CurrentUser extends User
{
    use Singleton;
    
    protected static string $service_alias = 'user.current';
    
    public function __construct( DBUserGatewayInterface $gateway )
    {
        parent::__construct(...[
            'sid'     => Cookie::get('sid'),
            'gateway' => $gateway
        ]);
    }
}