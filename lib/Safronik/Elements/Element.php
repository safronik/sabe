<?php

namespace Safronik\Elements;

use Safronik\Services\DB\Gateways\DBGatewayElement;
use Safronik\Apps\SiteStructure\Style\CSS;

class Element implements Elementable
{
    public string  $id;
    public string  $name;
    public string  $type;
    public string  $css_string;
    public CSS     $css_rules;
    
    public string  $content;
    
    public function __construct( DBGatewayElement $gateway, string $element_id )
    {
        $element_data = $gateway->getElementData( $element_id );
        
        $this->id   = $element_data['element_id']   ?? '';
        $this->name = $element_data['element_name'] ?? '';
        $this->type        = $element_data['type'];
        $this->css_string = $element_data['css']      ?? '';
        $this->css_rules  = new CSS( $element_data['css'] );
        $this->content    = $element_data['content']    ?? '';
    }
}