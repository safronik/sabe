<?php

namespace Safronik\Models\Services\RepositoryInterfaces;

use Safronik\Models\Entities\EntityObject;

interface EntityRepositoryInterface{
    
    /** Additional methods */
    // public function setEntity( $entity_classname ): void;
    
    /** CRUD */
    public function create( array $data ): array|EntityObject;
    public function read( array $condition = [], ?int $amount = null, ?int $offset = null ): array|EntityObject;
    public function save( EntityObject|array $items ): array;
    public function delete( $condition ): ?int;
    
    /** Additional */
    public function count( array $condition ): int;
    
}