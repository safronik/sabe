<?php

namespace Safronik\Services\DB;

use Safronik\Services\DB\Drivers\DBDriverInterface;

// Interfaces
use Safronik\Core\CodeTemplates\Interfaces\Serviceable;

// Code Templates (traits)
use Safronik\Core\CodeTemplates\Service;
use Safronik\Core\CodeTemplates\Singleton;

// Extensions (traits)
use Safronik\Services\DB\Extensions\PDOPreparedQueryExtension;
use Safronik\Services\DB\Extensions\PreparedQueryExtension;
use Safronik\Services\DB\Extensions\TableExtension;
use Safronik\Services\DB\Extensions\SimpleAccessExtension;
use Safronik\Services\DB\Extensions\QueryBuilder;

class DB implements Serviceable{
 
    // Code Templates
	use Singleton;
    use Service;
    
    // Extensions
    use TableExtension;
    use PreparedQueryExtension;
    // use SimpleAccessExtension;
    use PDOPreparedQueryExtension;
    use QueryBuilder;
    
    protected static string $service_alias = 'db';
    
    public DBDriverInterface $driver;
    
    /** @var string Query string */
	public $query = null;
    
    /** @var string Valid values are 'array'|'obj'|'num' */
    public string $response_mode = 'array';
    
	/** @var mixed DB result. Could be anything, depends on a driver implementation */
	public $result = '';
 
	/** @var int Number of affected rows */
	public int $rows_affected;
    
    /** @var int Number of selected rows  */
	private int $rows_selected;
 
	/** @var string Common DB prefix for all tables */
    public string $db_prefix = '';
	
	/** @var string Application DB prefix used only by current application */
    public $app_prefix = '';
	
	/** @var string $this->db_prefix and $this->app_prefix combined */
	public string $full_prefix;
    
    /**
     * @param DBConfig $config
     */
    protected function __construct( DBConfig $config )
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
    
    /**
     * Executes a query to DB and returns DB object for further processing
     *
     * @param string|null $query
     *
     * @return static
     */
    public function query( string $query = null ): static
    {
        $this->query         = $query ?? $this->query;
        
        $this->result        = $this->driver->q( $this->query );
        $this->rows_affected = $this->driver->getAffectedRowCount();
        $this->rows_selected = $this->driver->getSelectedRowCount();
        
        return $this;
    }

    /**
     * Fetch first column from query result
     *
     * @param string|null $response_mode
     *
     * @return array|object|void|null
     */
    public function fetch( string $response_mode = null )
    {
        return $this->driver->fetch( $response_mode ?: $this->response_mode );
    }
    
    /**
     * Fetch all result from query.
     * May receive raw or prepared query.
     *
     * @param string|null          $query
     * @param string|null          $response_mode
     * @param callable|string|null $fetch_argument
     *
     * @return array|object|null
     */
    public function fetchAll( string $query = null, string $response_mode = null, callable|string $fetch_argument = null ): object|array|null
    {
        if( $query ){
            $this->query( $query );
        }
		
        return $this->driver->fetchAll( $response_mode ?: $this->response_mode, $fetch_argument );
    }
    
    /**
     * Response mode setter
     *
     * @param string $response_mode
     *
     * @return static
     */
    public function setResponseMode( string $response_mode ): static
    {
        $this->response_mode = $response_mode;
        
        return $this;
    }
    
    /**
     * @param bool|int|string|null $value
     * @param string               $type
     *
     * @return array|int|string|null
     */
    protected function sanitize( bool|int|string|null $value, string $type = 'string' ): array|int|string|null
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
                
            // Consider empty type as a 'string' type
	        default:
				$sanitized_value = $this->sanitize( $value );
				break;
        }
        
        return $sanitized_value;
    }
    
    /**
     * @param bool|int|string $value
     * @param string          $type
     *
     * @return int|string
     * @throws \Exception
     */
    protected function castBinds( bool|int|string $value, string $type ): int|string
    {
        switch( $type ){
            case 'string':
                return (string)$value;
            case 'int':
                return (int)$value;
            case 'bool':
                return $value ? 'TRUE' : 'FALSE';
            case 'null':
                return 'NULL';
            default:
                throw new \Exception( 'Not supported cast type' );
        }
    }
}