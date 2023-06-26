<?php

namespace Safronik\Services\DB;

// Interfaces
use Safronik\Services\DB\InnerInterfaces\DBSimpleAccess;
use Safronik\Services\DB\InnerInterfaces\DBCustomRequestsInterface;
use Safronik\Services\DB\InnerInterfaces\DBHelperMethodsInterface;
use Safronik\Services\DB\InnerInterfaces\DBPreparedRequestsInterface;
use Safronik\Services\Serviceable;

// Traits
use Safronik\Core\CodeTemplates\Singleton;
use Safronik\Core\CodeTemplates\Service;

class DB implements DBSimpleAccess, DBCustomRequestsInterface, DBHelperMethodsInterface, DBPreparedRequestsInterface, Serviceable{
 
	use Singleton, Service;
    
    protected static string $service_alias = 'db';
    
    /**
	 * @var string
	 */
	public $query = null;
    
    /**
     * @var string Valid values are 'array'|'obj'|'num'
     */
    public $response_mode = 'array';
    
    /**
     * @var bool Show if the prepared query contains placeholders
     */
    public $query_is_prepared = false;
    
	/**
	 * @var
	 */
	public $result = '';
	
    /**
	 * @var
	 */
	public $execute_result = '';
 
	/**
	 * @var int
	 */
	public $rows_affected;
    
    /**
     * @var \Safronik\Services\DB\Drivers\DBDriverInterface
     */
    public $driver;
	
	/**
	 * Common DB prefix for all tables
	 *
	 * @var string
	 */
    public $db_prefix = '';
	
	/**
	 * Application DB prefix used only by current application
	 *
	 * @var string
	 */
    public $app_prefix = '';
	
	/**
	 * $this->db_prefix and $this->app_prefix combined
	 *
	 * @var string
	 */
	public $full_prefix;
	
    	/**
	 * @var int
	 */
	private $rows_selected;
    
    /**
     * @var string Shows if the placeholders named (:placeholder) or unnamed (?)
     */
    private $placeholders_type;
    
    /**
     * @param Config $config
     *
     * @return self
     */
    protected function __construct( DBConfigInterface $config )
    {
        $driver_namespace = __NAMESPACE__ . '\Drivers\\';
		switch( $config->driver ){
			case 'PDO':
	            $driver_name = $driver_namespace . 'PDO_v' . substr( phpversion('pdo'), 0, 1);
	            $this->driver = new $driver_name(
	                $config->dsn,
	                $config->username,
	                $config->password,
	                $config->options
	            );
				break;
			case 'Wordpress':
				$driver_name = $driver_namespace . $config->driver;
	            $this->driver    = new $driver_name(
					$config->connection
	            );
				break;
		}
  
		$this->db_prefix   = $config->db_prefix;
		$this->app_prefix  = $config->app_prefix;
		$this->full_prefix = $config->db_prefix . $config->app_prefix;
	}
    
    public static function initialize( ...$params ): void
    {
        self::getInstance( ...$params );
    }
    
    /**
	 * Safely replace placeholders in any part of query
     *
     * Doesn't create a prepared statement.
     * Look for $this->prepare if you want to make multiple similar queries.
     *
	 * @param string $query
	 * @param array  $values
	 *
	 * @return DB
     */
	public function prepare( $query, $values )
    {
        $this->query             = $query;
        $this->placeholders_type = strpos( $query, ' ? ' ) !== false ? 'unnamed' : 'named';
        if( $values ){
            $this->preparePlaceholders( $values );
        }
        return $this;
	}
    
    /**
     * Bind values to prepared query
     *
     * @param $values
     *
     * @return $this
     */
    public function preparePlaceholders( $values = array() )
    {
        if( $this->placeholders_type === 'named'){
            foreach( $values as $value_data ){
                $this->preparePlaceholder( $value_data[1], isset( $value_data[2] ) ?$value_data[2] : 'string', $value_data[0] );
            }
        }
        if( $this->placeholders_type === 'unnamed'){
            for($i = 1, $value_size = count($values); $i <= $value_size; $i++){
                $this->preparePlaceholder( $values[ $i - 1][0], isset( $values[ $i - 1][1] ) ? $values[ $i - 1][1] : 'string' );
            }
        }
        
        return $this;
    }

    private function preparePlaceholder( $value, $type = 'string', $name = null )
    {
        $sanitized_value = $this->sanitizeValue( $value, $type );
        if( $name ){
            $this->query = preg_replace( '@' . $name . '@', $sanitized_value, $this->query, 1 );
        }else{
            $this->query = preg_replace( '/\s?\?\s?/', ' ' . $sanitized_value . ' ', $this->query, 1 );
        }
    }
    
	/**
     * Executes a query to DB and returns DB object for further processing
     *
     * @param string $query
     *
     * @return DB
     */
    public function query( $query = null )
    {
        $this->query         = isset( $query ) ? $query : $this->query;
        $this->result        = $this->driver->q( $this->query );
        $this->rows_affected = $this->driver->getAffectedRowCount();
        $this->rows_selected = $this->driver->getSelectedRowCount();
        
        return $this;
    }
    
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
            $sql .= ' LIMIT ' . $this->sanitizeValue( $start, 'int' );
            if( isset( $amount ) ){
                $sql .= ',' . $this->sanitizeValue( $start, 'int' );
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
	
    private function createSubstitutionsFromInput( $input )
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

	/**
     * Create prepared statement for multiple queries
     *
     * Allows to make quick queries with different parameters
     * Prepare DB by making a query plan
     * Safely replace placeholders
     *
     * <br>
     * <b>Supports placeholder only for parameters ( no tables or columns names are supported )</b>
     *
	 *
	 * @param string $query
	 * @param array  $values
	 *
	 * @return DB
     */
	public function createPreparedQuery( $query = null, $values = [] )
    {
        $this->query             = isset( $query ) ? $query : $this->query;
        $this->query_is_prepared = $this->driver->prep( $this->query );
        $this->placeholders_type = strpos( $this->query, ' ? ' ) !== false ? 'unnamed' : 'named';
        if( $values ){
            $this->bindValues( $values );
        }
        
        return $this;
	}
    
    /**
     * Bind values to prepared query
     *
     * @param $values
     *
     * @return $this
     */
    private function bindValues( $values = array() )
    {
        if( $this->placeholders_type === 'named'){
            foreach( $values as $value_data ){
                $type = isset( $value_data[2] ) ? $value_data[2] : 'string';
                $this->driver->prepared_statement->bindValue( $value_data[0], $value_data[1], $this->driver->convertPlaceholdersType( $type ) );
            }
        }
        
        if( $this->placeholders_type === 'unnamed'){
            for($i = 1, $value_size = count($values); $i <= $value_size; $i++){
                $type = isset( $values[$i-1][1] ) ? $values[$i-1][1] : 'string';
                $this->driver->prepared_statement->bindValue( $i, $values[ $i - 1][0], $this->driver->convertPlaceholdersType( $type ) );
            }
        }
        
        return $this;
    }
	
    /**
     * Executes prepared statement
     *
     * @param $params
     *
     * @return $this
     */
    public function executePreparedQuery( $params = array() )
    {
        $this->execute_result = $this->driver->execute( $params );
        
        return $this;
    }
    
    /**
     * Fetch all result from query.
     * May receive raw or prepared query.
     *
     * @param string               $query
     * @param string               $response_mode
     * @param null|callable|string $fetch_argument
     *
     * @return array|object|null
     */
    public function fetchAll( $query = null, $response_mode = null, $fetch_argument = null )
    {
        if( $query ){
            $this->query( $query );
        }
		
        return $this->driver->fetchAll( $response_mode ?: $this->response_mode, $fetch_argument );
    }
    
    /**
     * Fetch first column from query result
     *
     * @param string|null $response_mode
     *
     * @return array|object|void|null
     */
    public function fetch( $response_mode = null )
    {
        return $this->driver->fetch( $response_mode ?: $this->response_mode );
    }
    
    /**
     * Response mode setter
     *
     * @param string $response_mode
     *
     * @return $this
     */
    public function setResponseMode( $response_mode )
    {
        $this->response_mode = $response_mode;
        
        return $this;
    }
	
    public function createTable( $table, $columns, $indexes = [], $if_not_exist = false ): bool
    {
        $sql = 'CREATE TABLE '
               . ( $if_not_exist ? 'IF NOT EXISTS' : '' )
               . ' `' . $table . '` (';
        
        // Add columns to request
        foreach ( $columns as $column ){
            $column['field'] = '`' . $column['field'] . '`';
            $sql .= implode(' ', $column) . ",\n";
        }
        
        // Add index to request
        foreach ( $indexes as $index ) {
            $sql .= $index['name'] === 'PRIMARY'
                ? implode(' ', $index) . ",\n"
                : $index['type'] . " {$index['name']} " . $index['body'] . ",\n";
        }
        
        $sql = substr($sql, 0, -2) . ');';
        var_dump( $sql);
        return $this
            ->query( $sql )
            ->isTableExists( $table );
    }
    
    
    
    public function alterTable( $table, $columns_create = [], $columns_change = [], $columns_drop = [], $indexes = [] ): bool
    {
        $sql = "ALTER TABLE `$table`";
        
        foreach( $columns_create as &$column ){
            $column['field'] = '`' . $column['field'] . '`';
            $sql .= ' ADD COLUMN ' . implode(' ', $column) . ",\n";
        } unset( $column );
        
        foreach( $columns_change as &$column ){
            $column['field'] = '`' . $column['field'] . '`';
            array_unshift( $column, $column['field'] );
            $sql .= ' CHANGE COLUMN ' . implode(' ', $column) . ",\n";
        } unset( $column );
        
        foreach( $columns_drop as $column ){
            $sql .= " DROP COLUMN `{$column}`,\n";
        }

        foreach( $indexes as $index ){
            $sql .= ' ADD ' . implode(' ', $index) . ",\n";
        }
        
        $sql = substr($sql, 0, -2);
        
        return ( $columns_create || $columns_change || $columns_drop || $indexes ) &&
               $this
                ->query( $sql )
                ->isTableExists( $table );
    }
    
    /**
     * Checks if the table exists
     *
     * @param $table_name string
     *
     * @return bool
     */
	public function isTableExists( $table_name )
    {
        return (bool) $this->prepare(
			'SHOW TABLES LIKE :table_name',
	        [
				[ ':table_name', $table_name, ],
	        ]
        )
	    ->query()
        ->fetchAll();
    }
	
	public function dropTable( $table_name )
	{
        return (bool) $this->prepare(
			'DROP TABLE :table_name',
	        [
				[ ':table_name', $table_name, 'serve_word' ],
	        ]
        )
	    ->query();
	}
	
	public function getTableScheme( $table )
	{
		return $this->prepare(
			'SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :table',
			[ [ ':table', $table, 'string' ] ]
		)
			->query()
			->fetchAll();
	}
	
    public function sanitizeValue($value, $type)
    {
        switch($type){
            case 'table':
                $sanitized_value = preg_replace( '/[^\w\d._-]/', '', $value);
                break;
            case 'column_name':
                $sanitized_value = preg_replace( '/[^\w\d._-]/', '', $value);
                break;
            case 'limit':
                $sanitized_value = preg_replace( '/\D/', '', $value);
                break;
            case 'order_by':
                $sanitized_value = preg_replace( '/[^\w\d._-]/', '', $value);
                break;
            case 'serve_word':
                $sanitized_value = preg_replace('/[^\w\s]/', '', $value);
                break;
            case 'string':
                $sanitized_value = $this->driver->sanitize( (string) $value );
                break;
            case 'int':
                $sanitized_value =  (int) $value;
                break;
            case 'bool':
                $sanitized_value =  $value ? 'TRUE' : 'FALSE';
                break;
            case 'null':
                $sanitized_value =  'NULL';
                break;
	        default:
				$sanitized_value = $this->sanitizeValue( $value, 'string' );
				break;
        }
        
        return $sanitized_value;
    }
	
	/**
	 * @param $value
	 * @param $type
	 *
	 * @return int|string
	 * @throws \Exception
	 */
    private function castBinds( $value, $type )
    {
        switch($type){
            case 'string':
                return (string) $value;
            case 'int':
                return (int) $value;
            case 'bool':
                return $value ? 'TRUE' : 'FALSE';
            case 'null':
                return 'NULL';
	        default:
				throw new \Exception('Not supported cast type');
        }
    }
}