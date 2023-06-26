<?php

namespace Safronik\Services\DBStructureHandler;

class TablesAnalyzer
{
	private SQLScheme $scheme_to_check;
    private array $table_not_exists = array();
    private array $exist_tables = array();
    private DBGatewayDBStructureInterface $db_gateway;
	
	public function __construct( DBGatewayDBStructureInterface $db_gateway, SQLScheme $scheme_to_check )
    {
        $this->db_gateway      = $db_gateway;
	    $this->scheme_to_check = $scheme_to_check;
		
        $this->checkCurrentScheme();
    }
	
    /**
     * Checking the existence of tables and non-existent tables
     * Filled fields of class
     */
    private function checkCurrentScheme()
    {
        foreach ( $this->scheme_to_check::get() as $scheme_table_name => $table_data ){
			
	        $table_name = $this->db_gateway->getPrefix() . $this->db_gateway->getAppPrefix() . $scheme_table_name;
	        if( ! $this->db_gateway->isTableExists( $table_name ) ){
		        $this->table_not_exists[] = $scheme_table_name;
	        } else{
		        $this->exist_tables[] = $scheme_table_name;
	        }
        }

        $this->exist_tables     = array_unique($this->exist_tables);
        $this->table_not_exists = array_unique($this->table_not_exists);
    }

    /**
     * @return array
     */
    public function getExistingTables()
    {
        return $this->exist_tables;
    }
	
    /**
     * Get non-exists tables
     */
    public function getNotExistingTables()
    {
        return $this->table_not_exists;
    }
}
