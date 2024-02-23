<?php

namespace ASecurity\Core\DB\Structure;

use ASecurity\Core\DB\SQLSchema;
use ASecurity\Core\DB\DB;

class TablesHadler
{
    /**
     * @var DB
     */
    private $db;
	private $scheme;

    public function __construct( $db, $scheme )
    {
        $this->db     = $db;
        $this->scheme = $scheme;
    }

    /**
     * Create Table by table name
     *
     * @param string $scheme_table_name
     *
     * @throws \Exception
     */
    public function createTable($scheme_table_name)
    {
	    $table_scheme = $this->scheme[ $scheme_table_name ];
	    $table_name   = $this->db->db_prefix . $this->db->app_prefix . $scheme_table_name;
	    $sql          = 'CREATE TABLE IF NOT EXISTS `' . $table_name . '` (';

        // Add columns to request
        foreach ( $table_scheme['columns'] as $column ) {
			
            // Giving the column default parameters
            $column_values = array_merge( SQLSchema::getDefaultField(), $column );

            $sql .= '`' . $column_values['field'] . '`'
                    . ' ' . $column_values['type']
                    . ($column_values['null'] === 'no' ? ' NOT NULL'                             : ' NULL')
                    . ($column_values['default']       ? ' DEFAULT ' . $column_values['default'] : '')
                    . ($column_values['extra']         ? ' ' . $column_values['extra']           : '')
                    . ",\n";
        }
	    
        // Add index to request
        foreach ( $table_scheme['indexes'] as $index ) {
            $sql .= $index['type'] . ' ' . $index['name'] . ' ' . $index['body'] . ",\n";
        }

        $sql = substr($sql, 0, -2) . ');';
		
        $this->db->query($sql);
    }
}
