<?php

namespace Safronik\Models\Services\User;

// Templates
use Safronik\CodePatterns\Generative\Singleton;
use Safronik\Globals\Cookie;

// Applied

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