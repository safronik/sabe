<?php

namespace Safronik\Layout\Page;

use Safronik\Core\DB\DB;
use Safronik\Core\CodeTemplates\Hydrator;

class Page
{
    use Hydrator;
    
    public $name = '';
    public $access = '';
    public $title = '';
    public $meta = '';
    public $modules = '';
    
    public function __construct( $name )
    {
        $page_data = $this->getPageData( $name );
        
        $this->hydrateFrom( $page_data );
    }
    
    private function getPageData( $name )
    {
        return DB::getInstance()
            ->select(
                'pages',
                [],
                [ 'name' => [ $name ], ]
            );
    }
    
    public function exists()
    {
        return (bool)$this->name;
    }
    
    public function draw()
    {
    
    }
}