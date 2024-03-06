<?php

namespace Safronik\Services\DB\Extensions\QueryBuilder\Operations;

trait Where{
    
    private string $table      = '';
    private array  $table_data = [];
    
    private string $where = '';
    private array  $where_data = [];
    
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
     * @param ?string $table
     *
     * Supported operators are: '=', '!=', '>', '<',' >=', '<=', 'in', 'like'
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function where( array $conditions, ?string $table = null ): static
    {
        if( ! $conditions ){
            return $this;
        }
        
        $table = $table ?? $this->table;
        
        $this->where .= $this->where
            ? "\n"
            : "WHERE\n"; // prepend key phrase if it's the first call
        
        $where = [];
        foreach( $conditions as $column => $condition ){
            
            $operator = ! is_array( $condition )
                ? '='
                : strtolower( $condition[0] );
            
            $operand = ! is_array( $condition )
                ? $condition
                : $condition[1];
            
            in_array( $operator, $this->allowed_operators, true )
                || throw new \Exception('Unsupported operator');
            
            switch( $operator ){
                
                case 'in':
                    
                    $column_placeholder = ":where_$column" . count( $this->where_data );
                    
                    // Adding operands via unnamed placeholders
                    foreach( $operand as $key => &$item ){
                        $column_value_in_placeholder                      = ":where_value_in_$column" . count( $this->where_data );
                        $this->where_data[ $column_value_in_placeholder ] = [ $item, ];
                        $item                                             = $column_value_in_placeholder;
                    } unset( $item );
                    
                    $operands_string  = '(' . implode( ',', $operand )  . ')';
                    
                    $where[] = "$table.$column_placeholder IN $operands_string"; // Add placeholders
                    $this->where_data[ $column_placeholder ] = [ $column, 'column_name', ];
                    break;
                    
                // Simple operands like,=,!=,>,<,>=,<=
                default:
                    
                    $column_placeholder       = ":where_$column" . count( $this->where_data );
                    $column_value_placeholder = ":where_value_$column" . count( $this->where_data );
                    
                    $this->where_data[ $column_placeholder ]       = [ $column, 'column_name', ];
                    $this->where_data[ $column_value_placeholder ] = [ $operand, ];
                    
                    $where[] = "$table.$column_placeholder $operator $column_value_placeholder"; // Add placeholders
            }
        }
        
        $this->where .= implode( ' AND ', $where );
        
        return $this;
    }
    
    public function and( $condition )
    {
        $this->where .= ' AND ';
        
        return $this->where( $condition );
    }
    
    public function or( $condition )
    {
        $this->where .= ' OR ';
        
        return $this->where( $condition );
    }
}