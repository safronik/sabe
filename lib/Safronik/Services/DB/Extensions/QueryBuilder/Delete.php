<?php

namespace Safronik\Services\DB\Extensions\QueryBuilder;

use Safronik\Services\DB\DB;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Limit;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\OrderBy;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Table;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Where;

final class Delete
{
    use Table;
    use Where;
    use OrderBy;
    use Limit;
    
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
        
        $this->db->prepare(
            "DELETE FROM $this->table\n$this->where\n$this->order_by\n$this->limit;",
            array_merge(
                $this->table_data,
                $this->where_data,
                $this->order_by_data
            )
        );
        
        $this->db->query( $this->db->query );
        
        return $this->db->getRowsAffected();
    }
}