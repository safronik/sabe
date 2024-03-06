<?php

namespace Safronik\Domains\Entities;

use Safronik\Domains\Values\InlineCSS;

class Element extends EntityObject{
    
    // Entity properties
    protected string $type;
    
    public static array $rules = [
        'type' => [ 'required', 'type' => 'string', 'content' => [ 'h1','h2','h3', ], 'length' => 64, ],
    ];
    
    public function __construct( $id, string $type )
    {
        $this->id   = $id;
        $this->type = $type;
    }
    
    // Immutable
    public function getType(): string
    {
        return $this->type;
    }
}