<?php

namespace Safronik\Services\DBStructureHandler;

class SQLScheme
{
	/**
     * Schema table prefix
     */
    protected $schema_prefix = '';
	
    /**
     * @var string[]
     */
	protected $field_standard = array(
		'field'   => '',
		'type'    => '',
		'null'    => 'yes',
		'default' => '',
		'extra'   => '',
	);

    /**
     * Set of SQL-schemas for tables in array
     * Set for all websites. Should be installed with a main database prefix
     *
     * @var array
     */
    protected $schemas = [];
    
    public function __construct( array $schema )
    {
        $this->schemas = $schema;
    }
    
    /**
	 * @return array
	 */
	public function get()
	{
		return $this->schemas;
	}
    
    /**
     * Searches and returns table schema
     *
     * @param string $table Name of called table
     *
     * @return array      Schema
     */
    public function getByTableName( $table ): array
    {
        ! array_key_exists( $table, $this->schemas ) &&
            throw new \Exception( "Schema doesn't contain table: $table" );
        
        return $this->schemas[ $table ];
    }

    /**
     * Return scheme prefix
     *
     * @return string
     */
    public function getSchemaPrefix(): string
    {
        return $this->schema_prefix;
    }

    /**
     * Return all tables names with schema prefix added.
     * Wrapper for self::getBlogTableNames() and self::getCommonTableNames()
     *
     * @param bool $with_schema_prefix
     *
     * @return array
     */
    public function getTableNames( bool $with_schema_prefix = true )
    {
        $table_names = array_keys( $this->schemas );
        
        if( $with_schema_prefix ){
            foreach( $table_names as &$table_name ){
                $table_name = $this->getSchemaPrefix() . $table_name;
            }
        }
        
        return $table_names;
    }
    
    /**
     * Return standard column fields for scheme
     *
     * @return string[]
     */
    public function getDefaultField(): array
    {
        return $this->field_standard;
    }
    
    public function setMissingColumnValuesToDefault( $column ): array
    {
        return array_merge( $this->getDefaultField(), $column );
    }
    
    public function convertColumnSchemaToSQLNotation( $column ): array
    {
        $column            = $this->setMissingColumnValuesToDefault( $column );
        $column['null']    = $column['null'] === 'no' ? 'NOT NULL' : 'NULL';
        $column['default'] = $column['default'] ? 'DEFAULT ' . $column['default'] : '';
        $column['extra']   = $column['extra'] ?: '';
        
        return $column;
    }
    
    public function convertTableSchemaToSQLNotation( $table_schema )
    {
        foreach( $table_schema['columns'] as &$column ){
            $column = $this->convertColumnSchemaToSQLNotation( $column );
        }
        
        return $table_schema;
    }

    public function getTableSchemaWithSQLNotation( $table_name )
    {
        return $this->convertTableSchemaToSQLNotation( $this->getByTableName( $table_name ) );
    }
    
	/**
	 * @param $table
	 * @param $column
	 *
	 * @return string|null
	 */
	public function getColumnType( $table, $column ): ?string
    {
		foreach( $this->get()[ $table ]['columns'] as $column_data ){
			if( in_array( $column, $column_data, true ) ){
				$type = $column_data['type'];
			}
		}
		
		return self::convertSQLTypeToPHPType( $type );
	}
    
    /**
     * @param $field_sgl_type
     *
     * @return string|null
     */
	public static function convertSQLTypeToPHPType( $field_sgl_type ): ?string
	{
		if(
			! empty( array_filter(
				['int', 'float', 'double', 'decimal', 'timestamp',],
				static function($needle) use ($field_sgl_type) {
					return stripos( $field_sgl_type, $needle) !== false;
				}
			))
		){
			return 'int';
		}
		if(
			! empty( array_filter(
				['varchar', 'text', 'json', 'char', 'date', 'time', 'year', 'set', 'enum',],
				static function($needle) use ($field_sgl_type) {
					return stripos( $field_sgl_type, $needle) !== false;
				}
			))
		){
			return 'string';
		}
		
		return null;
	}
    
    /**
     * In case of emergency use this method
     *
     * If you don't set the type size like this: INT(11) or BIGINT(20),
     *  it won't be equal to the actual size from DB ( DB always returns type with sizes ).
     *
     * So the schema will be always different from actual and DBStructureHandler will be forced to update this column,
     *  despite that it's already has the correct type size.
     *
     * @param $schema
     *
     * @return void
     */
    public function fixSchemaTypeSize( $schema )
    {
        foreach( $schema as $table ){
            
            $types = array_column( $table['columns'], 'type' );
            
            foreach( $types as $type ){
                
                // Compute length for *INT types
                if( preg_match( '@^([a-zA-Z]*(INT))(?!\(\d+\))\s?(UNSIGNED)?@', $type, $matches ) ){
                    $length = match ( $matches[1] ) {
                                  'TINYINT' => 3,
                                  'SMALLINT' => 5,
                                  'MEDIUMINT' => 8,
                                  'INT' => 10,
                                  'BIGINT' => 20,
                              } + ( ! empty( $matches[3] ) ? 1 : 0 );
                    continue;
                }
                
                // Compute length for BIT
                if( preg_match( '@^([a-zA-Z]*(BIT))(?!\(\d+\))@', $type, $matches ) ){
                    $length = 1;
                }
            }
            
            $type = preg_replace( '@' . $matches[1] . '@', $matches[1] . "($length)", $type );
        }
    }
}
