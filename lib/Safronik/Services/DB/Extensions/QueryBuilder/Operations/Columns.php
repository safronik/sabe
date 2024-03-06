<?php

namespace Safronik\Services\DB\Extensions\QueryBuilder\Operations;

trait Columns{
    
    private string $table      = '';
    private array  $table_data = [];
    
    private string $columns = '';
    private array  $columns_data = [];
    
        /**
     * Set columns to interact with
     *
     * @param array|string $columns array with columns names or comma-separated string values
     * @param string|null  $table   Use custom table for columns. !!! WITHOUT SANITIZATION !!!
     *
     * @return static
     */
    public function columns( array|string $columns, string $table = null ): static
    {
        // @todo multiple tables in request
        // $table && ! in_array( $this->tables ) && $this->table( $table );
        // $table = end($this->tables);
        
        $table = $table ?? $this->table;
        
        // Convert to array
        $columns = ! is_array( $columns )
            ? explode( ',', $columns )
            : $columns;
        
        // Append table name to columns: 'id' -> 'users.id'
        foreach( $columns as &$column ){
            
            if( $column === '*' ){
                
                $this->columns_data[ ":column_all" ] = [ '*', 'column_name' ];
                $column = "$table.:column_all";
                
                continue;
            }
            
            $this->columns_data[ ":column_$column" ] = [ $column, 'column_name' ];
            $column = "$table.:column_$column";
        }
        unset( $column );
        
        $this->columns .= implode( ',', $columns );
        
        return $this;
    }
}