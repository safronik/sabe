<?php

namespace Safronik\Services\DB\Extensions\QueryBuilder;

use Safronik\Services\DB\DB;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Columns;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Ignore;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\OnDuplicateKey;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Table;
use Safronik\Services\DB\Extensions\QueryBuilder\Operations\Values;

class Insert
{
    use Table;
    use Columns;
    use Ignore;
    use Values;
    use OnDuplicateKey;
    
    private DB $db;
    
    public function __construct( $table, DB $db )
    {
        $this->db = $db;
        
        $this->table( $table );
    }
    
    /**
     * Fires complied query and fetch all data from the result
     *
     * @return int
     * @throws \Exception
     */
    public function run(): int
    {
        $this->table   || throw new \Exception('No table set for request');
        $this->columns || throw new \Exception('No columns set for request');
        $this->values  || throw new \Exception('No values set for request');
        
        $this->db->prepare(
            "INSERT $this->ignore INTO $this->table\n($this->columns)\nVALUES\n$this->values\n$this->on_duplicate_key;",
            array_merge(
                $this->table_data,
                $this->columns_data,
                $this->values_data,
                $this->on_duplicate_key_data,
                $this->on_duplicate_key_value_data
            )
        );
        
        $this->db->query( $this->db->query );
        
        return $this->db->getRowsAffected();
    }
}