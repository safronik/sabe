<?php

namespace Safronik\Services\DB\Extensions\QueryBuilder;

use Safronik\Services\DB\DB;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Set;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Table;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Where;

final class Update
{
    use Table;
    use Where;
    use Set;
    
    private DB $db;
    
    public function __construct( $table, DB $db )
    {
        $this->db = $db;
        
        $this->table( $table );
    }
    
    /**
     * Get query with all data
     *
     * @throws \Exception
     *
     * @return string|null
     */
    public function run(): ?string
    {
        // Check obligatory params
        $this->table || throw new \Exception('No table set for request');
        $this->set   || throw new \Exception('No values set for request');
        
        $this->db->prepare(
            "UPDATE $this->table SET $this->set $this->where;",
            array_merge(
                $this->table_data,
                $this->set_columns,
                $this->set_values,
                $this->where_data
            )
        );
        
        $this->db->query( $this->db->query );
        
        return $this->db->getRowsAffected();
    }
}