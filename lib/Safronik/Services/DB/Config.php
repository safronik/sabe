<?php

namespace Safronik\Services\DB;

use Safronik\Core\CodeTemplates\Hydrator;

class Config implements DBConfigInterface{
    
    use Hydrator;
	
	/**
	 * DB class with connection, such as WPDB
	 *
	 * @var
	 */
	public $connection;
    public $driver     = 'PDO';
    public $hostname   = 'localhost';
    public $port       = 3306;
    public $charset    = 'utf8';
    public $database   = 'cms';
    public $username   = 'root';
    public $password   = 'root';
    public $dsn        = '';
    public $options    = [];
    public $db_prefix  = '';
	public $app_prefix = '';
    
    /**
     * @param array $params
     */
    public function __construct( $params = array() )
    {
        if( empty( $params ) ){
            // @todo use file storage instead of direct file attachment
            require_once 'db_default_params.php';
            $params = $db_default_params;
            unset( $db_default_params );
        }
        
        $this->hydrateFrom( $params );
        
		if( $this->driver === 'Wordpress'){
  
		}
		
		// DPO
        if( $this->driver === 'PDO' ){
    
			$this->dsn = "mysql:host=$this->hostname;dbname=$this->database;$this->charset=utf8;port=$this->port";
			
            $this->options = array(
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, // Handle errors as an exceptions
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC     // Set default fetch mode as associative array
            );
    
            define( 'DB_ASSOC', \PDO::FETCH_ASSOC );
            define( 'DB_ASSOC_I', \PDO::FETCH_ASSOC );
            define( 'DB_OBJECT', \PDO::FETCH_OBJ );
            define( 'DB_OBJECT_I', \PDO::FETCH_OBJ );
            define( 'DB_NUM', \PDO::FETCH_NUM );
        }
        
		// MySQLi
        if( $this->driver === 'mysqli' ){
        
        }
    }
}

