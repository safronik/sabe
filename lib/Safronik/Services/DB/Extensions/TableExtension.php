<?php

namespace Safronik\Services\DB\Extensions;

use Safronik\Core\Exceptions\Exception;

trait TableExtension{
	
    /**
     * Checks if the table exists
     *
     * @param $table string
     *
     * @return bool
     */
	public function isTableExists( string $table ): bool
    {
        return (bool) $this
            ->prepare(
                'SHOW TABLES LIKE :table_name;',
                [ ':table_name' => $table ]
            )
            ->query()
            ->fetchAll();
    }
    
    /**
     * Drops a table
     *
     * @param $table
     *
     * @return bool
     */
	public function dropTable( $table ): bool
    {
        return  ! $this->isTableExists( $table ) ||
                    ! $this->prepare(
                        'DROP TABLE :table_name',
                        [ ':table_name' => [ $table, 'serve_word' ], ] )
                    ->query()
                    ->isTableExists( $table );
	}
    
    public function createTable( $table, $columns, $indexes = [], $if_not_exist = false ): bool
    {
        ! $if_not_exist && $this->isTableExists( $table ) &&
            throw new Exception('Table already exists: ' . $table);
        
        $sql = 'CREATE TABLE '
               . ( $if_not_exist ? 'IF NOT EXISTS' : '' )
               . ' `' . $table . '` (';
        
        // Add columns to request
        foreach ( $columns as $column ){
            $column['field'] = '`' . $column['field'] . '`';
            $sql .= implode(' ', $column) . ",\n";
        }
        
        // Add index to request
        foreach ( $indexes as $index ) {
            $sql .= $index['name'] === 'PRIMARY'
                ? implode(' ', $index) . ",\n"
                : $index['type'] . " {$index['name']} " . $index['body'] . ",\n";
        }
        
        $sql = substr($sql, 0, -2) . ');';
        
        return $this
            ->query( $sql )
            ->isTableExists( $table );
    }
    
    public function alterTable( $table, $columns_to_create = [], $columns_to_change = [], $columns_to_drop = [], $indexes = [] ): bool
    {
        $sql = "ALTER TABLE `$table`";
        
        foreach( $columns_to_create as &$column ){
            $column['field'] = '`' . $column['field'] . '`';
            $sql .= ' ADD COLUMN ' . implode(' ', $column) . ",\n";
        } unset( $column );
        
        foreach( $columns_to_change as &$column ){
            $column['field'] = '`' . $column['field'] . '`';
            array_unshift( $column, $column['field'] );
            $sql .= ' CHANGE COLUMN ' . implode(' ', $column) . ",\n";
        } unset( $column );
        
        foreach( $columns_to_drop as $column ){
            $sql .= " DROP COLUMN `{$column}`,\n";
        }

        foreach( $indexes as $index ){
            $sql .= ' ADD ' . implode(' ', $index) . ",\n";
        }
        
        $sql = substr($sql, 0, -2);
        
        return ( $columns_to_create || $columns_to_change || $columns_to_drop || $indexes ) &&
               $this
                ->query( $sql )
                ->isTableExists( $table );
    }
	
    /*
	public function getTableSchema( $table ): array
	{
		$fields = $this
            ->setResponseMode( 'array' )
            ->prepare(
                [ [ ':table', $table, 'table' ] ],
                'SHOW COLUMNS FROM :table'
            )
			->query()
			->fetchAll();
        
        // Cast fields property to lower
        return $this->standardizeFieldsNames( $fields );
	}
    
    private function standardizeFieldsNames( $fields_to_standardize ): array
    {
        $fields = [];
        foreach( $fields_to_standardize as $filed_num => $field ){
            foreach( $field as $property => $value ){
                $property = strtolower( $property );
                $fields[ $filed_num ][ $property ] = $value;
            }
        }
        
        return $fields;
    }
    //*/
}