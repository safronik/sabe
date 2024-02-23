<?php

namespace ASecurity\Core\DB\Structure;

use ASecurity\Core\DB\DB;

class ColumnsHandler
{
    /**
     * @var DB
     */
    private $db;

    /**
     * @var array
     */
    private $relevant_schema;
    /**
     * @var string
     */
    private $alter_query;

    public function __construct( $db, $scheme, $scheme_table_name )
    {
        $this->db              = $db;
        $this->relevant_schema = $scheme[ $scheme_table_name ];
		$table_name            = $this->db->db_prefix . $this->db->app_prefix . $scheme_table_name;
        $this->alter_query     = "ALTER TABLE $table_name\n";
    }

    public function createColumns($columns = array())
    {
        foreach ( $columns as $column_name ) {
            foreach ( $this->relevant_schema['columns'] as $column ) {
                if ( $column_name === $column['field'] ) {
                    $column = array_merge(array('null' => 'YES', 'default' => '', 'extra' => ''), $column);

                    $this->alter_query .= 'ADD COLUMN '
                                          . '`' . $column['field'] . '`'
                                          . ' ' . $column['type']
                                          . ($column['null'] === 'no' ? ' NOT NULL' : ' NULL')
                                          . ($column['default'] ? ' DEFAULT ' . $column['default'] : '')
                                          . ($column['extra'] ? ' ' . $column['extra'] : '')
                                          . ",\n";
                }
            }
        }
    }

    public function dropColumns($columns = array())
    {
        foreach ( $columns as $column_name ) {
            $this->alter_query .= 'DROP COLUMN `' . $column_name . "`,\n";
        }
    }

    public function changeColumns($columns = array())
    {
        foreach ( $columns as $column_name ) {
            foreach ( $this->relevant_schema['columns'] as $column ) {
                if ( $column_name === $column['field'] ) {
                    $column = array_merge(array('null' => 'YES', 'default' => '', 'extra' => ''), $column);

                    $this->alter_query .= 'CHANGE COLUMN `' . $column_name . '`'
                                          . ' `' . $column['field'] . '`'
                                          . ' ' . $column['type']
                                          . ($column['null'] === 'no' ? ' NOT NULL' : ' NULL')
                                          . ($column['default'] ? ' DEFAULT ' . $column['default'] : '')
                                          . ($column['extra'] ? ' ' . $column['extra'] : '')
                                          . ",\n";
                }
            }
        }
    }

    public function assembleQuery($columns_to_create, $columns_to_change, $columns_to_delete)
    {
        $this->createColumns($columns_to_create);
        $this->changeColumns($columns_to_change);
        $this->dropColumns($columns_to_delete);
    }

    public function execute()
    {
        $this->alter_query = substr($this->alter_query, 0, -2) . ';';
        $this->db->query($this->alter_query);
    }
}
