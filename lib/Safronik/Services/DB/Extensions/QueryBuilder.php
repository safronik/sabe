<?php

namespace Safronik\Services\DB\Extensions;

trait QueryBuilder
{
    // Inner flags and data to compile a query
    private bool  $count = false;
    private int   $offset = 0;
    private int   $amount = 1000;
    private array $conditions = [];
    
    // Compiled string parts of the request
    private string $table = '';
    private string $columns = '*';
    private string $joins = '';
    private string $where = '';
    private string $order_by = '';
    private string $limit = '';
    
    private array $allowed_operators = [
        '=',
        '!=',
        '>',
        '<',
        '>=',
        '<=',
        'in',
        'like',
    ];
    
    /**
     * Set table to query from\to
     *
     * @param string $table
     *
     * @return static
     */
    public function table( string $table ): static
    {
        $this->table = $table;
        
        return $this;
    }
    
    /**
     * Alias of $this->table
     *
     * @param string $table
     *
     * @return $this
     */
    public function from( string $table ): static
    {
        $this->table = $table;
        
        return $this;
    }
    
    /**
     *  Alias of $this->table
     *
     * @param string $table
     *
     * @return $this
     */
    public function into( string $table ): static
    {
        $this->table = $table;
        
        return $this;
    }
    
    /**
     * Set columns to interact with
     *
     * @param array|string $columns    array with columns names or comma-separated string values
     * @param string|null  $table_name
     *
     * @return static
     */
    public function columns( array|string $columns, string $table_name = null ): static
    {
        // Convert to array
        $columns = ! is_array( $columns )
            ? explode( ',', $columns )
            : $columns;
        
        // Append table name to columns: 'id' -> 'users.id'
        array_walk(
            $columns,
            static function( &$item, $key, $table_name ){
                $item = $table_name . '.' . trim( $item );
            },
            $table_name ?? $this->table
        );
        
        $this->columns = implode( ',', $columns );
        
        return $this;
    }
    
    // /**
    //  * Alias of $this->columns
    //  *
    //  * @param array|string $columns    array with columns names or comma-separated string values
    //  * @param string|null  $table_name
    //  *
    //  * @return $this
    //  */
    // public function select( array|string $columns, string $table_name = null ): static
    // {
    //     return $this->columns( $columns, $table_name );
    // }
    
    /**
     * Appends join block to request
     *
     * @param array $joins
     *      'user' => [
     *            'classname' => Domain::class,
     *            'condition' => [ 'user_id' => 'id', ],
     *            'type'      => 'inner|outer|left|full outer|...'
     *            'columns'   => [ 'inn' ],
     *      ],
     *
     * @return static
     */
    public function join( array $joins ): static
    {
        foreach( $joins as $relation => &$join ){
            
            $join_table    = $join['classname'] instanceof ObjectEntity ? $join['classname']::table() : $join['classname'];
            $on            = "$this->table." . key( $join['condition'] ) . " = $join_table." . current( $join['condition'] );
            $type          = $join['type'] ?? 'INNER';
            $join_columns  = $join['columns'] ?? $join['classname']::_getColumns();
            // $where        .= $join['condition']
            //     ? ' AND ' . implode(
            //         ' AND ',
            //         array_map(
            //             static function( $column ) use ( $join_table, $conditions ){
            //                     return "$join_table.$column = ?";
            //                 },
            //             $join['condition']
            //         )
            //     )
            //     : '';
            $this->columns .= ', ' . implode(
                    ', ',
                    array_map(
                        function( $column ) use ( $join_table, $relation ){
                            return "$join_table.$column AS " . $this->wrap( "$relation.$column" );
                        },
                        $join_columns
                    )
                );
            $join = "$type JOIN $join_table ON $on";
        } unset( $join );
        
        $this->joins = implode( ' ', $joins );
        
        return $this;
    }
    
    /**
     * Set where string from passed array
     *
     * @param array $conditions Examples:
     *      [
     *          'column_to_compare' => [
     *              'in', // Operator
     *              ['string_value', 10, 'another_string_value'], // Operand
     *          ]
     *      ]
     *      [
     *           'column_to_compare' => [
     *               'like', // Operator
     *               ['string_val%'], // Operand
     *           ]
     *       ]
     *
     * Supported operators are: '=', '!=', '>', '<',' >=', '<=', 'in', 'like'
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function where( array $conditions = [] ): static
    {
        $where = [];
        foreach( $conditions as $column => &$condition ){
            
            // Make condition standard
            $condition = ! is_array( $condition )
                ? ['=', $condition]
                : $condition;
            
            $operator = strtolower( $condition[0] );
            $operand  = $condition[1];
            
            if( ! in_array( $operator, $this->allowed_operators, true ) ){
                throw new \Exception('Unsupported operator');
            }
            
            switch( $operator ){
                
                // Complex operand
                case 'in':
                    // Adding operands via unnamed placeholders
                    $this->conditions = array_merge( $this->conditions, $operand );
                    $operands_string  = '(' . trim( str_repeat( '?,', count( $operand ) ), ',' )  . ')';
                    $where[]          = "$this->table.$column IN $operands_string";
                    break;
                    
                // Simple operand
                default:
                    $this->conditions[] = $operand;
                    $where[]            = "$this->table.$column $operator ?";
            }
        } unset( $condition );
        
        $this->where = $where
            ? 'WHERE ' . implode( ' AND ', $where )
            : '';
        
        return $this;
    }
    
    /**
     * Compiles an order by block
     *
     * @param string $column
     * @param string $order
     *
     * @throws \Exception
     *
     * @return static
     */
    public function orderBy( string $column, string $order = 'desc' ): static
    {
        if( $column ){
            
            $order = strtolower( $order );
            ! in_array( $order, ['desc', 'asc'], true ) &&
                throw new \Exception('Order is not supported');
            
            $order          = strtoupper( $order );
            $this->order_by = " ORDER BY $column $order ";
        }
        
        return $this;
    }
    
    /**
     * Compiles limit block
     *
     * @param int $amount
     * @param int $offset
     *
     * @return $this
     */
    public function limit( int $amount = 1000, int $offset = 0 ): static
    {
        $this->limit = 'LIMIT ' . ( $offset ?? $this->offset ) . ',' . ( $amount ?? $this->amount );
        $this->offset = $offset;
        $this->amount = $amount;
        
        return $this;
    }
    
    /**
     * Returns first entry from set
     *
     * @throws \Exception
     *
     * @return array|int|object|null
     */
    public function one()
    {
        $this->count = false;
        
        return $this
            ->limit(1)
            ->runQuery();
    }
    
    /**
     * Returns all selected entries
     *
     * @throws \Exception
     *
     * @return array|int|object|null
     */
    public function many()
    {
        $this->count = false;
        
        return $this->do();
    }
    
    /**
     * Fires prepared query
     *
     * @return array|int|object|null
     * @throws \Exception
     */
    public function do()
    {
        return $this->runQuery();
    }
    
    /**
     * Returns count of entries considering the passed conditions
     *
     * @throws \Exception
     *
     * @return int
     */
    public function count()
    {
        $this->count = true;
        $this->limit = '';
        $this->columns = 'COUNT(*) as total';
        
        $db_result = $this
            ->setResponseMode( 'array' )
            ->runQuery();
        
        return (int) $db_result[0]['total'];
    }
    
    /**
     * Get query with all data
     *
     * @throws \Exception
     *
     * @return string|null
     */
    public function getQuery()
    {
        if( ! $this->table ){
            throw new \Exception('No table set for request');
        }
        
        $this->prepare(
            array_values( $this->conditions ),
            "SELECT $this->columns FROM $this->table $this->joins $this->where $this->order_by $this->limit"
        );
        
        return $this->query;
    }
    
    /**
     * Fires complied query and fetch all data from the result
     *
     * @throws \Exception
     *
     * @return object|array|null
     */
    private function runQuery(): object|array|null
    {
        if( ! $this->table ){
            throw new \Exception('No table set for request');
        }
        
        $this->query( $this->getQuery() );
        $this->cleanParameters();
        
        return $this->fetchAll();
    }
    
    /**
     * Resets the state of class
     * Should be run every time after eat =)
     *
     * @return void
     */
    private function cleanParameters()
    {
        $this->table      = '';
        $this->columns    = '*';
        $this->conditions = [];
        $this->joins      = '';
        $this->count      = false;
        $this->offset     = 0;
        $this->amount     = 1000;
        $this->limit      = '';
        $this->order_by   = '';
        $this->where      = '';
    }
    
    /**
     * Wrap string into the passed char
     *
     * @param string $string
     * @param string $char
     *
     * @return string
     */
    private function wrap( string $string, string $char = '"' ): string
    {
        return str_pad( $string, strlen( $string ) + 2, $char, STR_PAD_BOTH );
    }
}