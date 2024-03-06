<?php

namespace Safronik\Services\DB\Gateways;

use Safronik\Core\Sanitizer\DomainSanitizer;
use Safronik\Core\Validator\DomainValidator;
use Safronik\Domains\Entities\EntityObject;
use Safronik\Repositories\Interfaces\BaseRepositoryInterface;
use Safronik\Services\DB\DB;
use Safronik\Services\DB\Schemas\SchemasProvider;

class EntityRepositoryGateway extends AbstractDBGateway implements BaseRepositoryInterface{
    
    
    /** @var string|EntityObject Contains classname */
    private string|EntityObject $entity;
    private string $table;
    private bool $check = true;
    
    /**
     * @param string      $entity
     * @param DB          $db
     * @param string|null $prefix
     *
     * @throws \Exception
     */
    public function __construct( DB $db, string $entity, ?string $prefix = null )
    {
        $this->entity = $entity;
        $this->table  = SchemasProvider::getEntityTable( $entity );
        
        parent::__construct( $db, $prefix );
    }

    public function setEntity( $entity_classname ): void
    {
        $this->entity = $entity_classname;
    }
    
    /**
     * Creates an entity from given data.
     * Validates data by built-in entity rules and entity SQL-schema rules
     *
     * Validates only new entities. No validation if data comes from DB.
     *
     * @param array $data
     * @param bool  $validate
     * @param bool  $sanitize
     *
     * @return mixed
     */
    public function create( array $data ): array|EntityObject
    {
        $entities = [];
        foreach( $data as &$datum ){
            
            if( $this->check ){
                
                DomainValidator::validate(
                    $datum,
                    $this->entity::$rules // Validate by entity embedded rules
                );
                
                DomainSanitizer::sanitize(
                    $datum,
                    $this->entity::$rules // Sanitize by entity SQL-schema
                );
            }
            
            $entities[] = new $this->entity( ...$datum );
            
        } unset( $datum );
        
        $this->check = true;
        
        return count( $entities ) === 1
            ? $entities[0]
            : $entities;
    }
    
    /**
     * Returns an array of entities or single entity
     *
     * @param array    $condition
     * @param int|null $amount
     * @param int|null $offset
     *
     * @return EntityObject|EntityObject[]
     * @throws \Exception
     */
    public function read( array $condition = [], ?int $amount = null, ?int $offset = null ): array|EntityObject
    {
        $entities_data = $this->db
            ->from( $this->table )
            ->where( $condition )
            ->limit( $amount, $offset )
            ->select();
        
        $this->check = false;
        $entities    = $this->create( $entities_data );
        return $entities;
        return $amount !== 1
            ? $entities[0]
            : $entities;
    }
    
    /**
     * Saves fully valid entity to the DB
     *
     * @param EntityObject $item
     *
     * @return bool
     * @throws \Exception
     */
    public function save( EntityObject $item ): bool
    {
        $table  = strtolower( preg_replace( '@^.*?\\\\([a-zA-Z]+)$@', '$1', $item::class ) ) . 's';
        $values = array_filter( $item->toArray(), static function ( $val ){
            return $val !== null;
        });
        
        return $this->db
            ->into($table )
            ->columns( array_keys( $values ) )
            ->values( $values )
            ->onDuplicateKey( 'update', $values )
            ->insert();
        
        // TODO: Implement save() method.
    }
    
    /**
     * @param $condition
     *
     * @return int
     * @throws \Exception
     */
    public function delete( $condition ): ?int
    {
        return $this->db
            ->from( $this->table )
            ->where( $condition )
            ->delete();
    }
    
    /**
     * Counts entities in table
     *
     * @param array $condition
     *
     * @return int
     * @throws \Exception
     */
    public function count( array $condition = [] ): int
    {
        return $this->db
            ->from( $this->table )
            ->where( $condition )
            ->count();
    }
/*
    public function getSQLTypeValidationRules(): array
    {
        $column_names = array_column( SchemasProvider::getEntityColumns( $this->entity ), 'field');
        $column_types = array_column( SchemasProvider::getEntityColumns( $this->entity ), 'type');
        
        $rules = [];
        foreach( $column_names as $order_number => $field ){
            $rules[ $order_number ] = [
                'type' => \Safronik\Services\DBStructureHandler\SQLScheme::convertSQLTypeToPHPType( $column_types[ $order_number ] ),
            ];
        }
        
        return $rules;
    }
    
    public function getSQLContentSanitizeRules(): array
    {
        $entity_columns  = SchemasProvider::getEntityColumns( $this->entity );
        $column_names    = array_column( $entity_columns, 'field');
        
        return array_combine(
            $column_names,
            $entity_columns
        );
    }
//*/
}