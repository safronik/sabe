<?php

namespace ASecurity\Core\DB\Structure;

class Structure
{
	private $db;
	private $scheme;
	
	public function __construct( $db, $scheme )
	{
		$this->db     = $db;
		$this->scheme = $scheme;
	}
	
    public function fix(): bool
    {
        ! $this->scheme && throw new \Exception( "No scheme was given" );
        
		$tables_analyzer = new TablesAnalyzer( $this->db, $this->scheme );
		
        foreach ($tables_analyzer->getNotExistingTables() as $not_existing_table) {
            $db_tables_creator = new TablesHadler( $this->db, $this->scheme );
            $db_tables_creator->createTable($not_existing_table);
        }
		
        foreach ($tables_analyzer->getExistingTables() as $existing_table) {
            $column_analyzer = new ColumnsAnalyzer( $this->db, $this->scheme, $existing_table );

            if ($column_analyzer->changes_required) {
                $column_creator = new ColumnsHandler( $this->db, $this->scheme, $existing_table );
                $column_creator->assembleQuery(
                    $column_analyzer->columns_to_create,
                    $column_analyzer->columns_to_change,
                    $column_analyzer->columns_to_delete
                );
                $column_creator->execute();
            }
        }
	}
}