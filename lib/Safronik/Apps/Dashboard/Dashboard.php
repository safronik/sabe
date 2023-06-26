<?php

namespace Safronik\Apps\Dashboard;

use Safronik\Core\CodeTemplates\Interfaces\Installable;
use Safronik\Core\CodeTemplates\Installer;
use Safronik\Apps\SABE\Blocks\Block;
use Safronik\Services\DB\Gateways\DBGatewayBlock;
use Safronik\Services\DB\Gateways\DBGateways;
use Safronik\Apps\SABE\SABEbable;
use Safronik\Apps\SiteStructure\SABETrait;

class Dashboard extends \Safronik\Apps\App implements Installable
{
    use Installer;
    
    private $css;
    private $js;
    private Block $dashboard;
    
    public function __construct( $services = ['user.current'], array $options = [] )
    {
        parent::__construct( $services, $options );
        
        $this->dashboard = new Block(
            DBGateways::get( DBGatewayBlock::class ),
            'dashboard'
        );
        
        $this->css = $this->dashboard->getCSSRuleFromChildren();
        $this->js  = $this->dashboard->getJSFromChildren();
    }
}