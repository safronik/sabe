<?php

namespace Safronik\Services\DB\Extensions\QueryBuilder;

use Safronik\Services\DB\DB;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Columns;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Limit;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\OrderBy;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Table;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Where;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Join;

class Select
{
    use Table;
    use Columns;
    use Where;
    use OrderBy;
    use Join;
    use Limit;
    
    private DB $db;
    private Join $join_ext;
    
    public function __construct( $table, DB $db )
    {
        $this->db = $db;
        
        $this->table( $table );
    }
    
    /**
     * Returns count of entries considering the passed conditions
     *
     * @throws \Exception
     *
     * @return int
     */
    public function count(): int
    {
        $this->limit = '';
        $this->columns = 'COUNT(*) as total';
        
        $this->db->setResponseMode( 'array' );
        
        return (int) $this->run()[0]['total'];
    }

    
    /**
     * Fires complied query and fetch all data from the result
     *
     * @return object|array|null
     * @throws \Exception
     */
    public function run(): object|array|null
    {
        // Check obligatory params
        $this->table || throw new \Exception('No table set for request');
        
        // Append table.* to columns if it's empty
        $this->columns || $this->columns('*' );
        
        $this->db->prepare(
            // "SELECT\n$this->columns\nFROM\n$this->table\n$this->joins\n$this->where\n$this->order_by\n$this->limit;",
            "SELECT\n$this->columns\n$this->join_columns\nFROM\n$this->table\n$this->joins\n$this->where\n$this->order_by\n$this->limit;",
            array_merge(
                $this->columns_data,
                $this->table_data,
                $this->join_data,
                $this->where_data,
                $this->order_by_data
            )
        );
        
        $this->db->query( $this->db->query );
        
        return $this->db->fetchAll();
    }
}