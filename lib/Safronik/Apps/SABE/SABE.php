<?php

namespace Safronik\Apps\SABE;

// Interfaces
use Safronik\Core\CodeTemplates\Installer;
use Safronik\Core\CodeTemplates\Interfaces\Installable;

// Useful
use Safronik\Apps\SiteStructure\Page;
use Safronik\Services\DB\Gateways\DBGateways;
use Safronik\Services\Services;
use Safronik\Apps\Dashboard\Dashboard;
use Safronik\Apps\AppsManager\AppsManager;

/**
 * Service
 * Application
 * Block
 * Element
 *
 * @property \Safronik\Services\DB\DB            $db
 * @property \Safronik\Services\Visitor\Visitor  $visitor
 * @property \Safronik\Services\User\CurrentUser $user
 * @property \Safronik\Services\Request\Request  $request
 */
final class SABE extends \Safronik\Apps\App implements Installable
{
    use Installer;
    
    protected static string $slug = 'cms';
    protected int $inner_request_nonce = 0x0; // Hex format
    public    array $apps;
    
    public function __construct( ...$params )
    {
        parent::__construct( ...$params );
        
        $app_manager    = new AppsManager();
        $dashboard      = new Dashboard();
        
        // Get requested page
        $this->page = new Page(
            DBGateways::get( 'page'),
            $this->request->currentRoute(),
            '404'
        );
        
        // Check access level
        Services::get( 'user.current' )
                ->isAllowedAccessLevel( $this->page->access )
            && throw new \Exception( "Page {$this->page->name} is not allowed to access");
        
        $this->page->initHead();
            $this->page->initMeta();
            $this->page->initTitle();
            $this->page->initCSS();
            $this->page->initJS();
        $this->page->initContent();
            $this->user->isAdmin() && $dashboard = new Dashboard();
        
        $this->page->render();
    }
}