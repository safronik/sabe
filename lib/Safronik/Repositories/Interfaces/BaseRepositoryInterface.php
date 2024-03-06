<?php

namespace Safronik\Repositories\Interfaces;

use Safronik\Domains\Entities\EntityObject;

interface BaseRepositoryInterface{
    
    /** Additional methods */
    // public function setEntity( $entity_classname ): void;
    
    /** CRUD */
    public function create( array $data ): array|EntityObject;
    public function read( array $condition = [], ?int $amount = null, ?int $offset = null ): array|EntityObject;
    public function save( EntityObject $item ): bool;
    public function delete( $condition ): ?int;
    
    /** Additional */
    public function count( array $condition ): int;
    
}