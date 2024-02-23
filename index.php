<?php

use Safronik\Services\DB\Gateways\DBGatewayDBStructure;
use Safronik\Services\DBStructureHandler\DBStructureHandler;
use Safronik\Services\Services;

require_once 'constants.php';
require_once 'lib/autoloader.php';
require_once 'config/error_config.php';


$position = 'background: black; color: white;';
$position = explode( '; ', $position );
$position = array_map( fn( $rule ) => explode( ': ', trim( $rule, ';' ) ), $position );
$position = array_combine( array_column( $position, 0), array_column( $position, 1) );

$type = 'TINYINT UNSIGNED';

\Safronik\Services\DB\DB::initialize(
    new \Safronik\Services\DB\DBConfig([
        'hostname' => 'db',
        'database' => 'sabe',
        'username' => 'root',
        'password' => 'root',
        // 'port' => 3311,
    ])
);

$db = \Safronik\Services\DB\DB::getInstance();
$structure_handler = new DBStructureHandler(
    new DBGatewayDBStructure( $db )
);

$sch = new Safronik\Services\DBStructureHandler\SQLScheme( [
        'visitors' => [
            'columns' => [
                [ 'field' => 'visitor_id',        'type' => 'VARCHAR(64)',   'null' => 'no', ],
                [ 'field' => 'ip',                'type' => 'VARCHAR(15)',   'null' => 'no', ],
                [ 'field' => 'ip_decimal',        'type' => 'VARCHAR(15)',   'null' => 'no', ],
                [ 'field' => 'browser_signature', 'type' => 'VARCHAR(255)',  'null' => 'no', ],
                [ 'field' => 'user_agent',        'type' => 'VARCHAR(1024)', 'null' => 'no', ],
                [ 'field' => 'hits',              'type' => 'INT UNSIGNED',  'null' => 'no', 'default' => '1' ],
            ],
            'indexes' => [
                [ 'name' => 'PRIMARY', 'type' => 'KEY', 'body' => '(`visitor_id`)' ],
                [ 'name' => 'block_name', 'type' => 'UNIQUE INDEX', 'body' => '(`ip_decimal`)' ],
            ],
        ],
    ] );

$structure_handler
    ->setSchema( $sch )
    ->analyzeCurrentStructure()
    ->updateSchema( $sch );

die;

try{
    new Safronik\Core\Core();
    new \Safronik\Core\Router(
        new \Safronik\Services\Request\Request()
    );
    
    /** @var \Safronik\Services\DB\DB $db */
    $db = \Safronik\Services\Services::get( 'db');
}catch(\Exception $exception){
    
    if( preg_match( '@.able\s(\'.*?\')\sdoesn\'t exist@', $exception->getMessage(), $matches ) ){
        
        new \Safronik\views\base(
            "Module $matches[1] need to be installed" . ' <a href="/install">Install</a>?'
        );
        
        exit;
    }
    
    new \Safronik\views\base( $exception->getMessage() );
}