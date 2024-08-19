<?php

namespace Safronik\Models\Repositories;

use Safronik\DB\DB;
use Safronik\DB\Extensions\QueryBuilder\Operations\BaseOperation;
use Safronik\DB\Extensions\QueryBuilder\Select;
use Safronik\Helpers\SanitizerHelper;
use Safronik\Helpers\ValidationHelper;
use Safronik\Models\Entities\EntityObject;
use Safronik\Models\Entities\SchemaProviders\EntitySchemaProvider;
use Safronik\Models\Entities\ValueObject;
use Safronik\Models\Services\RepositoryInterfaces\EntityRepositoryInterface;

class EntityRepository extends BaseRepository implements EntityRepositoryInterface
{
    
    /** @var string|EntityObject Contains classname */
    protected string|EntityObject $entity;
    protected string $table;
    
    private bool  $check = true;
    
    /**
     * @param string|EntityObject $entity Entity classname or EntityObject
     * @param DB                  $db
     *
     * @throws \Exception
     */
    public function __construct( DB $db, string|EntityObject $entity )
    {
        parent::__construct( $db );
        
        $this->entity = $entity;
        $this->table  = ( new EntitySchemaProvider( $entity ) )->getEntityTable();
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
     *
     * @return EntityObject[]|EntityObject
     * @throws \Exception
     */
    public function create( array $data, bool $new = false ): array|EntityObject
    {
        $rules = $new
            ? $this->entity::getRulesWithout('id')
            : $this->entity::getRules();
        
        foreach( $data as &$entity_data ){
            
            if( $this->check ){
                ValidationHelper::validate( $entity_data, $rules ); // Validate by entity embedded rules
                SanitizerHelper::sanitize( $entity_data, $rules ); // Sanitize by entity SQL-schema
            }
            
            // foreach( $this->getSubEntities() as $prop_name => $sub_entity ){
            //
            //     if( $this->entity === $sub_entity ){
            //         continue;
            //     }
            //
            //     $sub_entity_data = $this->extractSubEntityData(
            //         $entity_data,
            //         ( new EntitySchemaProvider( $sub_entity ) )->getEntityTable()
            //     );
            //     $entity_data[ $prop_name ][] = new $sub_entity( ...$sub_entity_data );
            // }
            
            // var_dump( $entity_data);
            // die;
            
            $entities[] = new $this->entity( $entity_data );
            
        } unset( $entity_data );
        
        $this->check = true;
        
        return $entities ?? [];
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
        $request = $this->db
            ->select( $this->table )
            ->where( $condition )
            ->limit( $amount, $offset );
        
        foreach( $this->getSubObjects() as $sub_object ){
            
            // One to many
            if( $sub_object['type']::$rules ){
                $request = $this->appendSubObjectToRequest(
                    $request,
                    $core_table ?? $this->table,
                    $sub_entity
                );
            }
        }
        
        foreach( $this->getSubEntities() as $sub_entity ){
            
            // One to many
            if( $sub_entity::$rules ){
                $request = $this->appendSubObjectToRequest(
                    $request,
                    $core_table ?? $this->table,
                    $sub_entity
                );
            
            // Recursive relation
            }elseif( $sub_entity === $this->entity ){
                
                // Rewrite request if we have recursive relation
                $core_table = 'cte';
                $request    = $this->db
                    ->select( $core_table )
                    ->with(
                        $this->db
                            ->cte( $core_table )
                            ->anchor(
                                $this->db
                                    ->select( $this->table )
                                    ->where( $condition )
                            )
                            ->recursive(
                                $this->db
                                    ->select( [ $this->table, $core_table ])
                                    ->columns( '*', $this->table )
                                    ->where( [ [ [ $this->table, 'parent' ], '=', [ $core_table, 'id' ] ] ] )
                            )
                    )
                    ->limit( $amount, $offset );
                
            // Many to many
            }elseif( $sub_entity::$rules ){
                $request = $this->appendSubEntityToRequest(
                    $request,
                    $core_table ?? $this->table,
                    $sub_entity
                );
            }
        }
        // echo $request;
        // die;
        $entity_data = $request->run();
        $entity_data = $this->filterEntityData( $entity_data );
        $this->check = false;
        $entities    = $this->create( $entity_data );
        
        return $amount === 1
            ? $entities[0]
            : $entities;
    }
    
    private function getSubEntities(): array
    {
        return array_filter(
            array_combine(
                array_keys( $this->entity::$rules ),
                array_column( $this->entity::$rules, 'type' )
            ),
            static fn( $type ) => class_exists( $type ) && is_subclass_of( $type, EntityObject::class )
        );
    }
    
    private function getSubObjects(): array
    {
        return array_filter(
            $this->entity::$rules,
            static fn( $rule ) =>
                class_exists( $rule['type'] ) &&
                is_subclass_of( $rule['type'], ValueObject::class ) &&
                ! is_subclass_of( $rule['type'], EntityObject::class )
        );
    }
    
    private function extractSubEntityData( array &$entity_data, string $entity_table ): array
    {
        $sub_entity_data = [];
        foreach( $entity_data as $key => $entity_datum ){
            if( str_contains( $key, $entity_table ) ){
                $sub_entity_data[ trim( str_replace( $entity_table, '', $key ), '.' ) ] = $entity_datum;
                unset( $entity_data[ $key ] );
            }
        }
        
        return $sub_entity_data;
    }
    
    private function appendSubObjectToRequest( Select $request, string $core_table, string $sub_entity,  ): BaseOperation
    {
        $sub_entity_table = ( new EntitySchemaProvider( $sub_entity ) )->getEntityTable();
        $relation_table   = $this->table . '__' . $sub_entity_table;
        
        $request
            ->join(
                [ [ $relation_table, $this->table . '_id' ], '=', [ $core_table, 'id' ] ],
                'inner',
                false
            );
        
        return $request;
    }

    
    private function appendSubEntityToRequest( Select $request, string $core_table, string $sub_entity,  ): BaseOperation
    {
        $sub_entity_table = ( new EntitySchemaProvider( $sub_entity ) )->getEntityTable();
        $relation_table   = $this->table . '__' . $sub_entity_table;
        
        $request
            ->join(
                [ [ $relation_table, $this->table . '_id' ], '=', [ $core_table, 'id' ] ],
                'left',
                false
            )
            ->join(
                [ [ $sub_entity_table, 'id' ], '=', [ $relation_table, $sub_entity_table . '_id' ] ],
                'left',
            );
        
        return $request;
    }
    
    private function filterEntityData( array $data )
    {
        return array_map(
            static function( $datum ){
                unset( $datum['parent'] );
                return $datum;
            } ,
            $data
        );
    }
    
    /**
     * Saves fully valid entity to the DB
     *
     * @param EntityObject|array $items
     *
     * @return int[]|string[]
     * @throws \Exception
     */
    public function save( EntityObject|array $items ): array
    {
        // Recursion
        if( is_array( $items ) ){
            $inserted_ids = [];
            foreach( $items as $item ){
                $inserted_ids[] = $this->save( $item )[0];
            }
            
            return $inserted_ids;
        }
        
        // Base case
        $values = array_filter(
            $items->toArray(),
            static function ( $val ){
                return $val !== null;
            }
        );
        
        $this->db
            ->insert($this->table )
            ->columns( array_keys( $values ) )
            ->values( $values )
            ->onDuplicateKey( 'update', $values )
            ->run();
        
        return (array)$this->db
            ->query('SELECT last_insert_id() as id')
            ->fetch()['id'];
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
            ->delete( $this->table )
            ->where( $condition )
            ->run();
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
            ->select( $this->table )
            ->where( $condition )
            ->count();
    }
    
    /**
     * Building tree from request result
     *
     * @param array           $elements
     * @param int|string|null $parent_id
     * @param string          $key_id
     * @param string          $key_parent
     * @param string          $key_children
     *
     * @return array|mixed
     */
    protected function buildTree( array &$elements, int|string|null $parent_id = null, string $key_id = 'id', string $key_parent = 'parent', string $key_children = 'blocks' )
    {
        $branch = [];
        
        // Root
        if( $parent_id === null ){
            
            $root_key = array_search( null, array_column( $elements, $key_parent, $key_id ), true );
            $branch   = $elements[ $root_key ];
            
            unset( $elements[ $root_key ] );
            
            $branch[ $key_children ] = $this->buildTree( $elements, $branch[ $key_id ], $key_id, $key_parent, $key_children );
            
            return $branch;
        }
        
        // Children
        foreach( array_column( $elements, $key_parent, $key_id ) as $id => $parent ){
            
            if( $parent === $parent_id ){
                
                $branch[ $id ] = $elements[ $id ];
                
                unset( $elements[ $id ] );
                
                $branch[ $id ][ $key_children ] = $this->buildTree( $elements, $id, $key_id, $key_parent, $key_children );
            }
        }
        
        return $branch;
    }
    
    /*
    public function getSQLTypeValidationRules(): array
    {
        $column_names = array_column( SchemasProvider::getEntityColumns( $this->entity ), 'field');
        $column_types = array_column( SchemasProvider::getEntityColumns( $this->entity ), 'type');
        
        $rules = [];
        foreach( $column_names as $order_number => $field ){
            $rules[ $order_number ] = [
                'type' => \Safronik\_Services\DBMigrator\SQLScheme::convertSQLTypeToPHPType( $column_types[ $order_number ] ),
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