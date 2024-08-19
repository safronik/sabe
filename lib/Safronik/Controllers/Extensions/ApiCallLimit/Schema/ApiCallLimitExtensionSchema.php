<?php

namespace Extensions\ApiCallLimit\Schema;

use Safronik\DBMigrator\Objects\Column;
use Safronik\DBMigrator\Objects\Index;
use Safronik\DBMigrator\Objects\Schemas;
use Safronik\DBMigrator\Objects\Table;

class ApiCallLimitExtensionSchema extends Schemas{
    
    public function __construct( array $schemas = [] )
    {
        parent::__construct( self::getScheme() );
    }
    
    /**
     * @return Table[]
     * @throws \Safronik\DBMigrator\Exceptions\DBMigratorException
     */
    public static function getScheme(): array
    {
        return [
            new Table(
                'api_intervals',
                
                // Columns
                [
                    new Column( [
                        'field'   => 'id',
                        'type'    => 'VARCHAR(64)',
                        'null'    => 'no',
                        'comment' => 'Primary key',
                    ] ),
                    new Column( [
                        'field'   => 'start',
                        'type'    => 'INT(11)',
                        'null'    => 'no',
                        'comment' => 'Interval start timestamp',
                    ] ),
                    new Column( [
                        'field'   => 'calls',
                        'type'    => 'INT(11)',
                        'null'    => 'no',
                        'default' => 0,
                        'comment' => 'Amount of calls executed in the interval',
                    ] ),
                ],
                
                [
                    new Index( [
                        'key_name' => 'PRIMARY',
                        'columns'  => [ 'id' ],
                        'unique'   => true,
                        'type'     => 'BTREE',
                        'comment'  => 'Primary key',
                    ] ),
                    // ...
                ],
            ),
            
            new Table(
                'api_limits',
                
                // Columns
                [
                    new Column( [
                        'field'   => 'id',
                        'type'    => 'int(11)',
                        'null'    => 'no',
                        'comment' => 'Primary key',
                        'extra'   => 'AUTO_INCREMENT'
                    ] ),
                    new Column( [
                        'field'   => 'controller',
                        'type'    => 'VARCHAR(64)',
                        'null'    => 'no',
                        'comment' => 'Controller name',
                    ] ),
                    new Column( [
                        'field'   => 'method',
                        'type'    => 'VARCHAR(64)',
                        'null'    => 'no',
                        'comment' => 'Controller name',
                    ] ),
                    new Column( [
                        'field'   => 'limit',
                        'type'    => 'INT(11)',
                        'null'    => 'no',
                        'default' => 0,
                        'comment' => 'Amount of calls to execute in interval',
                    ] ),
                    new Column( [
                        'field'   => 'interval',
                        'type'    => 'INT(11)',
                        'null'    => 'no',
                        'default' => 0,
                        'comment' => 'Time interval to count calls',
                    ] ),
                ],
                
                // Indexes (optional)
                [
                    new Index( [
                        'key_name' => 'PRIMARY',
                        'columns'  => [ 'id' ],
                        'unique'   => true,
                        'type'     => 'BTREE',
                        'comment'  => 'Primary key',
                    ] ),
                    new Index( [
                        'key_name' => 'UNIQUE',
                        'columns'  => [ 'controller', 'method' ],
                        'unique'   => true,
                        'type'     => 'BTREE',
                        'comment'  => 'Unique combination of controller and method',
                    ] ),
                    // ...
                ],
            ),
        ];
    }
}