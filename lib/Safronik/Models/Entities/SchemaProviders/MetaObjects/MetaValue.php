<?php

namespace Safronik\Models\Entities\SchemaProviders\MetaObjects;

use Safronik\Models\Entities\Entity;
use Safronik\Models\Entities\Value;

class MetaValue extends MetaEntity{
    
    public MetaEntity $parent;
    
    public function __construct( Value|string $class, string $root_namespace, Entity|string $parent_class )
    {
        parent::__construct( $class, $root_namespace );
        
        $this->parent = new parent( $parent_class, $root_namespace );
        $this->table  = $this->parent->table . '__' . $this->table;
        $this->rules  = array_merge(
            [ $this->parent->table . '_id' => $this->parent->rules['id'] ],
            $this->rules
        );
        unset( $this->rules['id'] );
    }
}