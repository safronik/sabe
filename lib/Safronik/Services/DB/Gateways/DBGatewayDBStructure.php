<?php

namespace Safronik\Services\DB\Gateways;

use Safronik\Services\DBStructureHandler\DBGatewayDBStructureInterface;

class DBGatewayDBStructure extends AbstractDBGateway implements DBGatewayDBStructureInterface
{
    public function isTableExists( $table ): bool
    {
        return $this->db
            ->isTableExists( $table );
    }
    
    public function createTable( $table, $scheme ): bool
    {
        return $this->db
            ->createTable( $table, $scheme['columns'], $scheme['indexes'], true );
    }
    
    public function dropTable( $table ): bool
    {
        return $this->db
            ->dropTable( $table );
    }

    
    public function alterTable(
        $table,
        $columns_create = [],
        $columns_change = [],
        $columns_drop = [],
        $indexes = []
    ): bool{
        return $this->db
            ->alterTable( $table, $columns_create, $columns_change, $columns_drop, $indexes );
    }
    
    public function getTableColumns( $table ): array
    {
        return $this->db
            ->setResponseMode( 'array' )
            ->prepare( 'SHOW COLUMNS FROM :table', [ [ ':table', $table, 'table' ] ] )
            ->query()
            ->fetchAll();
    }
    
    public function getPrefix(): string
    {
        return $this->db->db_prefix;
    }
    
    public function getAppPrefix(): string
    {
        return $this->db->app_prefix;
    }
}