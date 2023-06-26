<?php

namespace Safronik\Services\DBStructureHandler;

class ColumnsAnalyzer
{
	/**
	 * @var DBGatewayDBStructureInterface
	 */
	private $db_gateway;
	
    /**
     * Table structure in fact
     *
     * @var array
     */
    private $actual_schema;
	
    /**
     * Table structure that should be
     *
     * @var array
     */
    private $relevant_schema;
	
    private $field_standard = [
		'field'   => '',
		'type'    => '',
		'null'    => 'yes',
		'default' => '',
		'extra'   => '',
	];
	
    public $columns_to_create;
    public $columns_to_delete;
    public $columns_to_change;
	
    public $changes_required;
    
    public function __construct( DBGatewayDBStructureInterface $db_gateway, SQLScheme $scheme, $scheme_table_name )
    {
        $this->db_gateway = $db_gateway;
        $table_name       = $this->db_gateway->getPrefix() . $this->db_gateway->getAppPrefix() . $scheme_table_name;
        
        $this->relevant_schema = $scheme::getByTableName( $scheme_table_name );
        $this->relevant_schema = $this->convertSchemaToStandard( $this->relevant_schema['columns'] );
        
        $this->actual_schema = $this->db_gateway->getTableColumns( $table_name );
        $this->actual_schema = $this->convertSchemaToStandard( $this->actual_schema );
        $this->execute();
        
        $this->changes_required = $this->columns_to_change || $this->columns_to_create || $this->columns_to_delete;
    }

    /**
     * Create columns and drop excess columns
     */
    public function execute()
    {
        $this->columns_to_create = array_diff(
            array_column($this->relevant_schema, 'field'),
            array_column($this->actual_schema, 'field')
        );
		
        $this->columns_to_delete = array_diff(
            array_column($this->actual_schema, 'field'),
            array_column($this->relevant_schema, 'field')
        );

        //var_dump( $this->relevant_schema);
        //var_dump( $this->actual_schema);
        $this->columns_to_change = array();
        foreach ( $this->relevant_schema as $relevant_column ) {
            foreach ( $this->actual_schema as $actual_column ) {
                if ( $relevant_column['field'] === $actual_column['field'] ) {
                    foreach ( array_keys($this->field_standard) as $field_param_name ) {
                        
                        if (
                            $relevant_column[$field_param_name] !== $actual_column[$field_param_name] &&
                            ! ( $field_param_name === 'default' && $relevant_column[$field_param_name] === 'null' && $actual_column[$field_param_name] === '' )
                        ) {
                            $this->columns_to_change[] = $relevant_column['field'];
                        }
                    }
                }
            }
        }
        
        $this->columns_to_change = array_unique($this->columns_to_change);
    }

    private function convertSchemaToStandard($schema)
    {
        $tmp_schema = array();
		
        foreach ( $schema as $fields_num => $fields ) {
			
            foreach ( $fields as $field_name => $field_value ) {
				
                $tmp_field_name  = strtolower($field_name);
                $tmp_field_value = '';
				
                if ( is_string($field_value) && $field_value !== 'null' ) {
                    $tmp_field_value = strtolower($field_value);
                    $tmp_field_value = preg_replace('@[\'"]@', '"', $tmp_field_value);
                    $tmp_field_value = preg_replace('@[\s]@', '', $tmp_field_value);
                    $tmp_field_value = preg_replace('@^[\'"](.*?)[\'"]$@', '$1', $tmp_field_value);
                }

                if ( ! array_key_exists($tmp_field_name, $this->field_standard) ) {
                    continue;
                }

                $tmp_schema[$fields_num][$tmp_field_name] = $tmp_field_value;
            }
            
            $tmp_schema[$fields_num] = array_merge($this->field_standard, $tmp_schema[$fields_num]);
        }

        return $tmp_schema;
    }
}
