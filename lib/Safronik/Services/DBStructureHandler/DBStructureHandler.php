<?php

namespace Safronik\Services\DBStructureHandler;

use Safronik\Core\CodeTemplates\Interfaces\Serviceable;
use Safronik\Core\CodeTemplates\Service;

class DBStructureHandler implements Serviceable
{
    use Service;
    
    protected static string $service_alias = 'db.structure';
    protected static string $gateway_alias = 'db.structure';
    
	private DBGatewayDBStructureInterface $gateway;
	private ?SQLScheme $schema;
    private ?TablesAnalyzer $tables_analyzer;
    
    private array $tables_to_create = [];
    private array $tables_to_update = [];
    
    private bool $is_analyzed = false;
    
    public function __construct( DBGatewayDBStructureInterface $gateway )
	{
        $this->gateway = $gateway;
	}
	
    public function analyzeCurrentStructure( ?SQLScheme $schema = null ): static
    {
        $schema && $this->setSchema( $schema );
        
        ! $this->schema &&
            throw new \Exception('No schema defined to analyze. Use self::setSchema() to define it.');
        
        $this->tables_analyzer  = new TablesAnalyzer( $this->gateway, $this->schema );
        $this->tables_to_create = $this->tables_analyzer->getNotExistingTables();
        
        foreach( $this->tables_analyzer->getExistingTables() as $existing_table ){
            
            $columns_analyzer = new ColumnsAnalyzer( $this->gateway, $this->schema, $existing_table );
            
            if( $columns_analyzer->changes_required ){
                
                $this->tables_to_update[ $existing_table ] = [
                    'create'  => $columns_analyzer->columns_to_create,
                    'update'  => $columns_analyzer->columns_to_change,
                    'delete'  => $columns_analyzer->columns_to_delete,
                    'indexes' => [], // @todo add index analysis to ColumnsAnalyzer
                ];
            }
        }
        
        $this->is_analyzed = true;
        
        return $this;
    }
    
    /**
     * Iteratively creates tables
     *
     * @param array $tables_to_create
     *
     * @return bool
     */
    private function createTables( array $tables_to_create ): bool
    {
        // Filter out created tables one by one
        return (bool) array_filter(
            $tables_to_create,
            function( $table_to_create ){
                
                $table_scheme           = $this->schema->getTableSchemaWithSQLNotation( $table_to_create );
                $table_name_with_prefix = $this->gateway->getPrefix() . $this->gateway->getAppPrefix() . $table_to_create;
                
                return $this->gateway->createTable( $table_name_with_prefix, $table_scheme );
            }
        );
    }
    
    /**
     * Iteratively updates tables
     *
     * @param $tables_to_update
     *
     * @return bool
     */
    private function updateTables( $tables_to_update ): bool
    {
        // Filter out updated tables one by one
        return (bool) array_filter(
            $tables_to_update,
            function( $table_data, $table_to_update ){
                return $this->updateTableStructure( $table_to_update, $table_data );
            },
            ARRAY_FILTER_USE_BOTH
        );
    }
    
    public function updateSchema( ?SQLScheme $schema = null ): bool
    {
        $schema && $this->setSchema( $schema );
        
        ! $this->schema &&
            throw new \Exception('No schema defined to analyze. Use self::setSchema() to define it.');
        
        $this->analyzeCurrentStructure();
        
        return
            ( ! $this->tables_to_create || $this->createTables( $this->tables_to_create ) ) &&
            ( ! $this->tables_to_update || $this->updateTables( $this->tables_to_update ) );
	}
    
    public function dropSchema( ?SQLScheme $schema = null ): bool
    {
        $schema && $this->setSchema( $schema );
        
        ! $this->schema &&
            throw new \Exception('No schema defined to analyze. Use self::setSchema() to define it.');
        
        $out = true;
        foreach( $this->schema->get() as $scheme_table_name => $table_data ){
            $out = $out && $this->gateway->dropTable( $scheme_table_name );
        }
        
        return $out;
	}
    
    private function updateTableStructure( $table_name, $table_data ): bool
    {
        $table_scheme           = $this->schema->getByTableName( $table_name );
        $columns_to_create      = $this->getSQLSchemeByTableAndColumn( $table_data['create'], $table_scheme );
        $columns_to_change      = $this->getSQLSchemeByTableAndColumn( $table_data['update'], $table_scheme );
        $columns_to_drop        = $this->getSQLSchemeByTableAndColumn( $table_data['delete'], $table_scheme );
        $indexes                = $this->getSQLSchemeByTableAndColumn( $table_data['indexes'], $table_scheme );
        $table_name_with_prefix = $this->gateway->getPrefix() . $this->gateway->getAppPrefix() . $table_name;
        
        return $this->gateway->alterTable( $table_name_with_prefix, $columns_to_create, $columns_to_change, $columns_to_drop, $indexes );
    }
    
    private function getSQLSchemeByTableAndColumn( $columns, $table_scheme )
    {
        foreach ( $columns as &$column ){
            foreach( $table_scheme['columns'] as $column_scheme ){
                if( $column === $column_scheme['field'] ){
                    $column = $this->schema->convertColumnSchemaToSQLNotation( $column_scheme );
                }
            }
        }
        
        return $columns;
    }
    
    /**
     * @param SQLScheme $schema
     *
     * @return static
     */
    public function setSchema( SQLScheme $schema ): static
    {
        $this->schema      = $schema;
        $this->is_analyzed = false;
        
        return $this;
    }
}