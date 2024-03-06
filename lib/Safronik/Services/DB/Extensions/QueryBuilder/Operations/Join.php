<?php

namespace Safronik\Services\DB\Extensions\QueryBuilder\Operations;

use Safronik\Services\DB\DB;
use Safronik\Services\DB\Extensions\QueryBuilder\Select;

trait Join
{
    private Select $select;
    
    private int    $join_count = 1;
    private string $joins = '';
    private string $join_columns = '';
    private array  $join_data;
    
    private string $on;
    
    private array  $allowed_join_types = ['inner','outer','left','full outer',];
    private string $type;
    
    /**
     * Appends join block to request
     *
     * @param array $joins
     *      'user' => [
     *            'table'     => 'table name',
     *            'condition' => [ 'user_id' => 'id', ],
     *            'type'      => 'inner|outer|left|full outer|...'
     *            'columns'   => [ 'inn' ],
     *      ],
     *
     * @return static
     */
    public function join( string $join_table, array $on, string $type = 'inner', array $columns = [] ): static
    {
        // Process type
        $type = $this->joinType( $type );
        
        // Process join table name
        $join_table_placeholder = ':join_table_' . $this->join_count;
        $this->join_data[ $join_table_placeholder ]  = [ $join_table, 'table' ];
        
        // Process ON condition
        $on_left_condition_placeholder  = ':on_left_condition_placeholder_'  . $this->join_count;
        $on_right_condition_placeholder = ':on_right_condition_placeholder_' . $this->join_count;
        $this->join_data[ $on_left_condition_placeholder ]  = [ key(     $on ), 'column_name' ];
        $this->join_data[ $on_right_condition_placeholder ] = [ current( $on ), 'column_name' ];
        
        // INNER JOIN secondary_table ON primary_table.id = secondary_table.parent_id
        $join = "$type JOIN $join_table_placeholder ON $this->table.$on_left_condition_placeholder = $join_table_placeholder.$on_right_condition_placeholder";
        
        // Process columns
        $this->join_columns .= $this->joinColumns( $columns, $join_table_placeholder, $join_table );
    
        $this->joins .= "\n $join";
        $this->join_count++;
        
        return $this;
    }
    
    private function joinType( $type ): string
    {
        ! in_array( strtolower( $type ), $this->allowed_join_types, true ) &&
            throw new \Exception("Join type '$type' is not allowed");
        
        return strtoupper( $type );
    }
    
    private function joinColumns( $columns, $join_table_placeholder, $join_table ): string
    {
        // Get all columns of join table if they aren't set
        if( ! $columns ){
            $schema = $this->db
                ->setResponseMode( 'array' )
                ->prepare(
                    'SHOW COLUMNS FROM :table;',
                    [ ':table' => [ $join_table, 'table' ] ]
                )
                ->query()
                ->fetchAll();
            $columns = array_column( $schema, 'Field' ) ?: array_column( $schema, 'field' );
        }
        
        // Append join columns to select statement, to parse the results later.
        // For example: join_table.column as "join_table.column"
        return ', ' . implode(
            ', ',
            array_map(
                static function( $column ) use ( $join_table_placeholder ){
                    return "$join_table_placeholder.$column AS \"$join_table_placeholder.$column\"";
                },
                $columns
            )
        );
    }
}