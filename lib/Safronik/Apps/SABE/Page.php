<?php

namespace Safronik\Apps\SiteStructure;

use Safronik\Services\Page\DBGatewayPageInterface;
use Safronik\Core\CodeTemplates\Hydrator;
use Safronik\Services\DB\Gateways\DBGatewayBlock;
use Safronik\Services\Services;
use Safronik\Apps\SABE\Blocks\Block;

class Page
{
    use Hydrator;
    
    public string $name;
    public array  $blocks;
    public array  $meta;
    public string $title;
    
    public function __construct( DBGatewayPageInterface $gateway, string $page, string $default_page = '404' )
    {
        $this->hydrateFrom(
            $gateway->getPageData( $page ) ?: $gateway->getPageData( $default_page )
        );
    }
    
    private function initBlocks( string $type )
    {
        $this->blocks = array_map(
            fn( $block_name ) => new Block( new DBGatewayBlock( Services::get( 'db' ) ), $block_name ),
            $this->blocks
        );
    }
}