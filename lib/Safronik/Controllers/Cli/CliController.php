<?php

namespace Safronik\Controllers\Cli;

use Safronik\CodePatterns\Structural\DI;
use Safronik\Controllers\Controller;
use Safronik\Globals\Server;
use Safronik\Views\Cli\CliView;

abstract class CliController extends Controller{
    
    protected string $root;
    
    protected function init(): void
    {
        $this->root = dirname( Server::get('argv')[0] );
    }
    
    public function methodHelp(): void
    {
        DI::get( CliView::class )->renderMessage( $this->getAvailableActions() );
    }
}