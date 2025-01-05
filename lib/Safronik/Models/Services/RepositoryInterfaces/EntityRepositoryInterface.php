<?php

namespace Safronik\Models\Services\RepositoryInterfaces;

use Safronik\Models\Entities\Entity;
use Safronik\Models\Entities\Value;

interface EntityRepositoryInterface{
    
    /** Additional methods */
    // public function setEntity( $entity_classname ): void;
    
    /** CRUD */
    public function create( Value|Entity $current ): void;
    public function read( array $condition = [], ?int $amount = null, ?int $offset = null ): array|Entity;
    public function save( array|Value|Entity $entities ): void;
    public function delete( $condition ): ?int;
    
    /** Additional */
    public function count( array $condition ): int;
}