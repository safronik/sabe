<?php

namespace boostra\repositories;

interface RepositoryInterface{
    
    /** CRUD */
    public function create( $data );
    public function read( $condition );
    public function save( $item, $data = null ): bool;
    public function delete( $condition ): ?int;
    
    /** Additional */
    public function count( $condition ): int;
    
}