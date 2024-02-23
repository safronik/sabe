<?php

namespace Safronik\Services\DB\Extensions;

trait SimpleAccessExtension{
	
    	/**
	 * @param $table   string
	 * @param $columns string[]
	 *                 [
     *                         []
	 *                 ]
	 * @param $where   array[]
	 *
	 * @return array|object|null
	 */
	public function select( $table, $columns = [], $where = [], $start = null, $amount = null )
	{
		$substitutions = [ [ ':table', $this->full_prefix . $table, 'table' ] ];
		
		if( $columns ){
			$columns_substitutions = $this->createSubstitutionsFromInput( $columns );
			$substitutions = array_merge(
				$substitutions,
				$columns_substitutions['substitutions']
			);
		}else{
			$columns_substitutions['placeholders'] = '*';
		}
		$sql = 'SELECT ' . $columns_substitutions['placeholders'] . ' FROM :table';
  
        // Where
		if( $where ){
			foreach( $where as $item => $value ){
				$substitution_names[] = [
					':' . $item,
					$item,
					'serve_word'
				];
				$substitution_values[] = [
					':' . $item . '_value',
					$value[0],
					isset( $value[1] ) ? $value[1] : 'string'
				];
				$placeholders[]  = ':' . $item . ' = :' . $item . '_value';
			}
			
			$substitutions = array_merge(
                $substitutions,
                $substitution_names  ?? [],
                $substitution_values ?? []
            );
			
			$sql .= ' WHERE ' . implode(' AND ', $placeholders );
		}
        
        // Limit
        if( isset( $start ) ){
            $sql .= ' LIMIT ' . $this->sanitize( $start, 'int' );
            if( isset( $amount ) ){
                $sql .= ',' . $this->sanitize( $start, 'int' );
            }
        }
        
        $this->prepare( $sql, $substitutions )
			 ->query();
        
        $this->result = $this->result
            ? $this->fetchAll()
            : [];
        
        $this->result = count($this->result) === 1
            ? $this->result[0]
            : $this->result;
        
		return $this->result;
	}
	
	public function update( $table, $columns, $where )
	{
        $substitutions = [ [ ':table', $this->full_prefix . $table, 'table' ] ];
        
        $sql = 'UPDATE :table';
        
        $placeholders = [];
        $substitution_names = [];
        $substitution_values = [];
        
        foreach( $columns as $name => $value ){
			$substitution_names[] = [
				':' . $name,
				$name,
				'column_name'
			];
			$substitution_values[] = [
				':' . $name . '_value',
				$value[0],
                $value[1] ?? 'string'
			];
            $placeholders[]  = ':' . $name . ' = :' . $name . '_value';
		}
        
        $substitutions = array_merge(
            $substitutions,
            $substitution_names  ?? [],
            $substitution_values ?? []
        );
        $sql .= ' SET ' . implode(', ', $placeholders );
        
        // Where
		if( $where ){
            
            $placeholders = [];
            $substitution_names = [];
            $substitution_values = [];
            
			foreach( $where as $item => $value ){
				$substitution_names[] = [
					':' . $item,
					$item,
					'column_name'
				];
				$substitution_values[] = [
					':' . $item . '_value',
					$value[0],
                    $value[1] ?? 'string'
				];
				$placeholders[]  = ':' . $item . ' = :' . $item . '_value';
			}
			
			$substitutions = array_merge(
                $substitutions,
                $substitution_names  ?? [],
                $substitution_values ?? []
            );
			
			$sql .= ' WHERE ' . implode(' AND ', $placeholders );
		}
        
        return $this
            ->prepare( $sql, $substitutions )
            ->query()
            ->rows_affected;
	}
	
	/**
	 * Wrapper to use SQL INSERT INTO
	 *
	 * @param $table            string
	 * @param $columns          array[] Except the following structure:
	 *                          [
	 *                          'column_name'  => [ 'value_to_insert',  'type_to_cast_in'  ]
	 *                          'column_name2' => [ 'value_to_insert2', 'type_to_cast_in2' ]
	 *                          ...
	 *                          ]
	 * @param array $modifiers Excepts the following structure with optional elements:
     *                          [
     *                              'ignore',
     *                              'on_duplicate_key' => [
     *                                  'increment' => [
     *                                      'columns' => string[],
     *                                  ],
     *                                  'update' => [
     *                                      'column_name' => [ 'new_value', 'type' ]
     *                                  ]
     *                              ]
     *                          ]
	 *
	 * @return int
	 */
	public function insert( $table, $columns, $modifiers = [] )
	{
		$substitution_names   = [];
		$substitution_values  = [];
		
		foreach( $columns as $item => $value ){
			$substitution_names[] = [
				':' . $item,
				$item,
				'serve_word'
			];
			$substitution_values[] = [
				':' . $item . '_value',
				$value[0],
				isset( $value[1] ) ? $value[1] : 'string'
			];
		}
		
		$names_placeholders  = implode(', ', array_column( $substitution_names, 0 ) );
		$values_placeholders = implode(', ', array_column( $substitution_values, 0 ) );
		$substitutions = [
			[ ':table', $this->full_prefix . $table, 'serve_word' ]
		];
		$substitutions = array_merge(
			$substitutions,
			$substitution_names,
			$substitution_values
		);
		
        $on_duplicate_key_callback = static function() use ( $modifiers ){
            
                    $output = " ON DUPLICATE KEY UPDATE\n";
                    
                    // Increment specific columns
                    if( isset( $modifiers['on_duplicate_key']['increment'] ) ){
                        $columns_to_increment = $modifiers['on_duplicate_key']['increment'];
                        foreach( $columns_to_increment as $column_to_increment ){
                            $output .= "$column_to_increment = $column_to_increment + 1,\n";
                        }
                    }
                    
                    // Update specific columns with values
                    if( isset( $modifiers['on_duplicate_key']['update'] ) ){
                        $columns_to_increment = $modifiers['on_duplicate_key']['update'];
                        foreach( $columns_to_increment as $column_name => $data ){
                            $output .= "$column_name = {$data['0']},\n";
                        }
                    }
                    
                    return substr( $output, 0, -2);
                };
        
		$ignore           = in_array( 'ignore',    $modifiers, true ) ? 'IGNORE' : '';
		$on_duplicate_key = isset( $modifiers['on_duplicate_key'] )
            ? $on_duplicate_key_callback()
            : '';
		
		$this
            ->prepare("INSERT $ignore INTO :table ($names_placeholders) VALUES ($values_placeholders) $on_duplicate_key;", $substitutions )
            ->query()
            ->rows_affected;
	}
	
	public function delete( $table, $where )
	{
        $sql           = 'DELETE FROM :table';
        $substitutions = [ [ ':table', $this->full_prefix . $table, 'serve_word' ] ];
        
        if( $where ){
			foreach( $where as $item => $value ){
				$substitution_names[] = [
					':' . $item,
					$item,
					'serve_word'
				];
				$substitution_values[] = [
					':' . $item . '_value',
					$value[0],
					isset( $value[1] ) ? $value[1] : 'string'
				];
				$placeholders[]  = ':' . $item . ' = :' . $item . '_value';
			}
			
			$substitutions = array_merge(
                $substitutions,
                $substitution_names,
                $substitution_values
            );
			
			$sql .= ' WHERE ' . implode(' AND ', $placeholders );
		}
        
        return $this
            ->prepare( $sql, $substitutions )
            ->query()
            ->rows_affected;
	}
	
    private function createSubstitutionsFromInput( $input ): array
    {
		$substitution       = [];
		foreach( $input as $item ){
			$substitution[] = [
				':' . $item,
				$item,
				'serve_word'
			];
		}
		$substitution_names = implode( ', ', array_column( $substitution, 0 ) );
		
		return [
			'substitutions' => $substitution,
			'placeholders'  => $substitution_names,
		];
	}
}