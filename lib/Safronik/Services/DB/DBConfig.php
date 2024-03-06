<?php

namespace Safronik\Services\DB;

use Safronik\Core\CodeTemplates\Hydrator;
use Safronik\Core\Exceptions\Exception;

class DBConfig{
    
    use Hydrator;
    
    /**
     * DB class with connection, such as WPDB
     *
     * @var
     */
    public $connection;
    public $driver = 'PDO';
    public $hostname = 'localhost';
    public $port = 3306;
    public $charset = 'utf8';
    public $database = '';
    public $username = '';
    public $password = '';
    public $dsn = '';
    public $options = [];
    public $db_prefix = '';
    public $app_prefix = '';
    
    /**
     * @param array $params
     *
     * @throws Exception
     */
    public function __construct( $params = [] )
    {
        // Getting config from deafult file if params is not passed
        if( empty( $params ) ){
            
            // @todo use file storage instead of direct file attachment
            /** @var array $db_config */
            require_once \SABE_CONFIG . 'db.php';
            $params = $db_config;
            unset( $db_config );
        }
        
        $this->hydrateFrom( $params );
        
        
        $this->standardizeDriverName( $this->driver );
        $this->setConfigForDriver( $this->driver );
        
        // var_export( $this );
        // die;
    }
    
    public function standardizeDriverName( $driver ): void
    {
        $driver       = strtolower( $driver );
        $this->driver = match ( $driver ) {
            'wordpress' => 'Wordpress',
            'pdo'       => 'PDO',
            'mysqli'    => 'mysqli',
            default     => 'unknown',
        };
    }
    
    public function setConfigForDriver( $driver ): void
    {
        match ( $driver ) {
            'Wordpress' => $this->setWordpressConfig(),
            'PDO'       => $this->setPDOConfig(),
            'mysqli'    => $this->setMySQLiConfig(),
            default     => throw new Exception('Passed driver is not supported: ' . $driver )
        };
    }
    
    /**
     * Set WordPress config
     *
     * @return void
     */
    private function setWordpressConfig()
    {
        global $wpdb;
        $this->connection = $wpdb;
        $this->db_prefix  = $wpdb->prefix;
    }
    
    /**
     * Set PDO config
     *
     * @return void
     */
    private function setPDOConfig()
    {
        $this->dsn = "mysql:host=$this->hostname;dbname=$this->database;charset=$this->charset;port=$this->port";
        
        $this->options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION, // Handle errors as an exceptions
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC     // Set default fetch mode as associative array
        ];
        
        define( 'DB_ASSOC',    \PDO::FETCH_ASSOC );
        define( 'DB_ASSOC_I',  \PDO::FETCH_ASSOC );
        define( 'DB_OBJECT',   \PDO::FETCH_OBJ );
        define( 'DB_OBJECT_I', \PDO::FETCH_OBJ );
        define( 'DB_NUM',      \PDO::FETCH_NUM );
    }
    
    /**
     * Set MySQLi config
     *
     * @return void
     */
    private function setMySQLiConfig()
    {
        $this->connection = new \mysqli(
            $this->hostname, $this->username, $this->password, $this->database, $this->port
        );
        $this->connection->set_charset( $this->charset );
    }
}