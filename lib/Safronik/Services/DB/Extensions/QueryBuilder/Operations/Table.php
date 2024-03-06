<?php

namespace Safronik\Services\DB\Extensions\QueryBuilder\Operations;

trait Table{
    
    private string $table      = '';
    private array  $table_data = [];
    
    /**
     * Set table to query from\to
     *
     * @param string $table
     *
     * @return static
     */
    private function table( string $table ): void
    {
        $this->table      = ':table';
        $this->table_data = [ ':table' => [ $table, 'table' ] ];
    }

}