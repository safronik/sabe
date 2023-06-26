<?php

namespace Safronik\Services\DBStructureHandler;

use Safronik\Services\Serviceable;
use Safronik\Core\CodeTemplates\Service;

class DBStructureHandler implements Serviceable
{
    use Service;
    
    protected static string $service_alias = 'db.structure';
    protected static string $gateway_alias = 'db.structure';
    
	private DBGatewayDBStructureInterface $gateway;
	private ?SQLScheme $scheme;
    private ?TablesAnalyzer$tables_analyzer;
	
	public function __construct( DBGatewayDBStructureInterface $gateway, ?SQLScheme $scheme = null )
	{
        $this->gateway         = $gateway;
        $this->scheme          = $scheme ?? null;
        $this->tables_analyzer = $scheme ? new TablesAnalyzer( $this->gateway, $this->scheme ) : null;
	}
	
    public function fix(): bool
    {
        ! $this->scheme && throw new \Exception( "No scheme was given" );
        
        $out = $this->createNotExistingTables();
        
        foreach( $this->tables_analyzer->getExistingTables() as $existing_table ){
            
            $column_analyzer = new ColumnsAnalyzer( $this->gateway, $this->scheme, $existing_table );
            
            if( $column_analyzer->changes_required ){
                
                $out = $out && $this->fixTableStructure(
                    $existing_table,
                    $column_analyzer->columns_to_create,
                    $column_analyzer->columns_to_change,
                    $column_analyzer->columns_to_delete
                );
            }
        }
        
        return $out;
	}
    
    public function drop(): bool
    {
        ! $this->scheme && throw new \Exception( "No scheme was given" );
        
        $out = true;
        foreach( $this->scheme::get() as $scheme_table_name => $table_data ){
            $out = $out && $this->gateway->dropTable( $scheme_table_name );
        }
        
        return $out;
	}

    
    private function createNotExistingTables(): bool
    {
        $out = true;
        
        foreach( $this->tables_analyzer->getNotExistingTables() as $not_existing_table ){
            
            $table_scheme = $this->scheme::getTableSchemaWithSQLNotation( $not_existing_table );
            $table_name   = $this->gateway->getPrefix() . $this->gateway->getAppPrefix() . $not_existing_table;
            
            $out = $out && $this->gateway->createTable( $table_name, $table_scheme );
        }
        
        return $out;
    }
    
    private function fixTableStructure( $table, $columns_to_create, $columns_to_change, $columns_to_drop, $indexes = [] ): bool
    {
        $table_scheme = $this->scheme::getByTableName( $table );
    
        $columns_to_create = $this->getSQLSchemeByTableAndColumn( $columns_to_create, $table_scheme );
        $columns_to_change = $this->getSQLSchemeByTableAndColumn( $columns_to_change, $table_scheme );
        $columns_to_drop   = $this->getSQLSchemeByTableAndColumn( $columns_to_drop, $table_scheme );
        //$indexes           = $this->getSQLSchemeByTableAndColumn( $indexes, $table_scheme );
        $table_name        = $this->gateway->getPrefix() . $this->gateway->getAppPrefix() . $table;
        
        return $this->gateway->alterTable( $table_name, $columns_to_create, $columns_to_change, $columns_to_drop, $indexes );
    }
    
    public function getSQLSchemeByTableAndColumn( $columns, $table_scheme )
    {
        foreach ( $columns as &$column ){
            foreach( $table_scheme['columns'] as $column_scheme ){
                if( $column === $column_scheme['field'] ){
                    $column = $this->scheme::convertColumnSchemaToSQLNotation( $column_scheme );
                }
            }
        }
        
        return $columns;
    }
    
    /**
     * @param SQLScheme $scheme
     */
    public function setScheme( SQLScheme $scheme ): self
    {
        $this->scheme          = $scheme;
        $this->tables_analyzer = new TablesAnalyzer( $this->gateway, $this->scheme );
        
        return $this;
    }
}