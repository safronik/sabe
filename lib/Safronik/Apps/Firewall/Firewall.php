<?php

namespace Safronik\Apps\Firewall;

use Safronik\Core\CodeTemplates\Interfaces\Installable;
use Safronik\Core\CodeTemplates\Installer;

class Firewall extends \Safronik\Apps\App
{
    protected static string $slug_string = 'firewall';
    
    public function __construct( $services = [], array $options = [] )
    {
        parent::__construct( $services, $options );
    }
}