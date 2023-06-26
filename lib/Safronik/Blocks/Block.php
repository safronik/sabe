<?php

namespace Safronik\Blocks;

use Safronik\Elements\Element;
use Safronik\Apps\SiteStructure\Style\CSS;

class Block
{
    public string  $id;
    public string  $name;
    public string  $type;
    public string  $css_string;
    public CSS     $css_rules;
    
    public string  $content;
    
    /**
     * @var Block[]
     */
    public array    $blocks;
    
    /**
     * @var Element[]
     */
    public array    $elements;
    
    public function __construct( DBGatewayBlockInterface $gateway, $block_name )
    {
        $block_data = $gateway->getBlockData( $block_name );
        
        if( empty( $block_data ) ){
            throw new \Exception( "No data found for block '$block_name'");
        }
    
        $this->id         = $block_data['block_id'] ?? '';
        $this->name       = $block_data['block_name'] ?? '';
        $this->type       = $block_data['tag'];
        $this->css_string = $block_data['css'] ?? '';
        $this->css_rules  = new CSS( $block_data['css'] );
        $this->content    = $block_data['content'] ?? '';
        $this->blocks     = $block_data['children'] ? json_decode( $block_data['children'], true ) : [];
    }
    
    public function render()
    {
        $pattern = '<%s id="%s" style="%s">%s</%s>';
        echo sprintf( $pattern, $this->type, $this->id, $this->css_string, $this->content, $this->type );
    }
    
    /**
     * @return Block[]|Element[]
     */
    public function getChildren(): array
    {
        return array_merge( $this->blocks, $this->elements );
    }
    
    public function getCSSRuleFromChildren(): string
    {
        $out = $this->css_string;
        
        foreach( $this->getChildren() as $child )
        {
            $out .= $child->css_string();
        }
        
        return $out;
    }
}