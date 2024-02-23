<?php

namespace ASecurity\Core\DB\Structure;

use ASecurity\Core\DB\DB;

class TablesAnalyzer
{
	/**
	 * @var mixed|null
	 */
	private $scheme_to_check;
	
    /**
     * @var array Tables which aren't exist
     */
    private $table_not_exists = array();

    /**
     * @var array Tables which exist
     */
    private $exist_tables = array();

    /**
     * @var DB
     */
    private $db;
	
	public function __construct( $db, $scheme_to_check )
    {
        $this->db = $db;
	    $this->scheme_to_check = $scheme_to_check;
		
        $this->checkCurrentScheme();
    }
	
    /**
     * Checking the existence of tables and non-existent tables
     * Filled fields of class
     */
    private function checkCurrentScheme()
    {
        foreach ( $this->scheme_to_check as $scheme_table_name => $table_data ){
			
	        $table_name = $this->db->db_prefix . $this->db->app_prefix . $scheme_table_name;
			
	        if( ! $this->db->isTableExists( $table_name ) ){
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
