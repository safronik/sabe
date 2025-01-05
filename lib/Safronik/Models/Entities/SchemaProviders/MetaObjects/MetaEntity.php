<?php

namespace Safronik\Models\Entities\SchemaProviders\MetaObjects;

use Safronik\Models\Entities\Entity;
use Safronik\Models\Entities\Rule;
use Safronik\Models\Entities\Value;

class MetaEntity{
    
    public Entity|Value|string $class;
    public string              $root_namespace;
    
    /** @var Rule[] */
    public array  $rules;
    public string $path;
    public array  $route;
    public string $table;
    
    public function __construct( Value|Entity|string $class, string $root_namespace, Entity|string $parent_class = null )
    {
        $this->class          = $class;
        $this->root_namespace = $root_namespace;

        $this->rules = $class::rules();
        $this->path  = $class::getPath( $root_namespace );
        $this->route = $class::getRoute( $root_namespace );
        $this->table = $class::getTable( $root_namespace );
    }
    
}