<?php

namespace Safronik\Domains\Entities;

use Safronik\Domains\Values\InlineCSS;

class Block extends EntityObject{
    
    // Entity properties
    protected string     $name;
    protected string     $tag;
    protected ?string    $parent;
    protected ?string    $content;
    protected ?string    $css;
    
    public static array $rules = [
        'id'       => [ 'required', 'type' => 'string',              'length' => 64,   'content' => '@^[a-z]+$@', ],
        'name'     => [ 'required', 'type' => 'string',              'length' => 64,   'content' => '@^[a-zA-Z0-9_-]+$@', ],
        'tag'      => [ 'required', 'type' => 'string',              'length' => 8,    'content' => [ 'div','header','footer','main','article','aside','section','nav' ], ],
        'parent'   => [             'type' => 'string',              'length' => 64,   'content' => '@^[a-z]+$@', ],
        'content'  => [             'type' => 'string',              'length' => 1024, ],
        'css'      => [             'type' => 'string',              'length' => 1024, 'default' => 'width: 100px; height: 100px;' ],
        'elements' => [             'type' => '[]' . Element::class, 'length' => 32, ],
        'blocks'   => [             'type' => '[]' . Block::class,   'length' => 32, ],
    ];
    
    public function __construct( $id, string $name, string $tag, ?string $parent = null, ?string $content = null, ?string $css = null, ?array $blocks = null, ?array $elements = null )
    {
        $this->id       = $id;
        $this->name     = $name;
        $this->tag      = $tag;
        $this->parent   = $parent;
        $this->css      = $css;
        $this->content  = $content;
        // $this->elements = $elements;
        // $this->blocks   = $blocks;
    }
    
    public function toArray(): array
    {
        $output = [];
        foreach( $this as $key => $value ){
            $output[ $key ] = $value;
        }
        
        return $output;
    }
    
    // Immutable
    public function getId(): string
    {
        return $this->name;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getTag(): string
    {
        return $this->tag;
    }

    // Mutable
    public function getParent(): string
    {
        return $this->parent;
    }
    public function setParent( string $parent ): void
    {
        $this->content = $parent;
    }
    
    public function getContent(): string
    {
        return $this->content;
    }
    public function setContent( string $content ): void
    {
        $this->content = $content;
    }
    
    public function getCss(): string
    {
        return $this->css;
    }
    public function setCss( string $css ): void
    {
        $this->css = $css;
    }
    
    public function getElements(): array|string
    {
        return $this->elements;
    }
    public function setElements( array|string $elements ): void
    {
        $this->elements = $elements;
    }
    
    public function getBlocks(): array|string
    {
        return $this->blocks;
    }
    public function setBlocks( array|string $blocks ): void
    {
        $this->blocks = $blocks;
    }
}