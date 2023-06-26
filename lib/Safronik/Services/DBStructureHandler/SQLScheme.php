<?php

namespace Safronik\Services\DBStructureHandler;

class SQLScheme
{
	/**
     * Schema table prefix
     */
    protected static $schema_prefix = '';
	
    /**
     * @var string[]
     */
	protected static $field_standard = array(
		'field'   => '',
		'type'    => '',
		'null'    => 'yes',
		'default' => '',
		'extra'   => '',
	);

    /**
     * Set of SQL-schemas for tables in array
     * Set for all websites. Should installed with a main database prefix
     *
     * @var array
     */
    protected static $schemas = [];
    
    public function __construct( array $scheme )
    {
        self::$schemas = $scheme;
    }
    
    /**
	 * @return array
	 */
	public static function get()
	{
		return static::$schemas;
	}
    
    /**
     * Searches and returns table schema
     *
     * @param string $table Name of called table
     *
     * @return array      Schema
     */
    public static function getByTableName( $table )
    {
        $schemas__all = static::get();
        
        if( array_key_exists( $table, $schemas__all ) ){
            return $schemas__all[ $table ];
        }
    }

    /**
     * Return scheme prefix
     *
     * @return string
     */
    public static function getSchemaPrefix()
    {
        return static::$schema_prefix;
    }

    /**
     * Return all tables names with schema prefix added.
     * Wrapper for self::getBlogTableNames() and self::getCommonTableNames()
     *
     * @param bool $with_schema_prefix
     *
     * @return array
     */
    public static function getTableNames( $with_schema_prefix = true )
    {
        $table_names = array_keys( static::$schemas );
        
        if( $with_schema_prefix ){
            foreach( $table_names as &$table_name ){
                $table_name = self::getSchemaPrefix() . $table_name;
            }
        }
        
        return $table_names;
    }
    
    /**
     * Return standard column fields for scheme
     *
     * @return string[]
     */
    public static function getDefaultField()
    {
        return static::$field_standard;
    }
    
    public static function setMissingColumnValuesToDefault( $column )
    {
        return array_merge( self::getDefaultField(), $column );
    }
    
    public static function convertColumnSchemaToSQLNotation( $column )
    {
        $column            = self::setMissingColumnValuesToDefault( $column );
        $column['null']    = $column['null'] === 'no' ? 'NOT NULL' : 'NULL';
        $column['default'] = $column['default'] ? 'DEFAULT ' . $column['default'] : '';
        $column['extra']   = $column['extra'] ?: '';
        
        return $column;
    }
    
    public static function convertTableSchemaToSQLNotation( $table_schema )
    {
        foreach( $table_schema['columns'] as &$column ){
            $column = self::convertColumnSchemaToSQLNotation( $column );
        }
        
        return $table_schema;
    }

    public static function getTableSchemaWithSQLNotation( $table_name )
    {
        return self::convertTableSchemaToSQLNotation( self::getByTableName( $table_name ) );
    }
    
	/**
	 * @param $table
	 * @param $column
	 *
	 * @return string|null
	 */
	public static function getColumnType( $table, $column ): ?string
    {
		foreach( self::get()[ $table ]['columns'] as $column_data ){
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
}
