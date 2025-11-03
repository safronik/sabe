<?php

namespace Safronik\Models\Repositories;

use Safronik\DB\DB;
use Safronik\Models\Entities\Entity;
use Safronik\Models\Entities\Obj;
use Safronik\Models\Entities\SchemaProviders\RelationSchemaProvider;
use Safronik\Models\Entities\Value;
use Safronik\Models\Repositories\EntityRepository\EntityRepositoryReader;
use Safronik\Models\Services\RepositoryInterfaces\EntityRepositoryInterface;

class EntityRepository extends BaseRepository implements EntityRepositoryInterface
{
    use EntityRepositoryReader;

    protected string $table;
    /** @var Entity|string Contains classname */
    protected Entity|string $entity;
    
    /**
     * @param string|Entity $entity Entity classname or EntityObject
     * @param DB                  $db
     *
     * @throws \Exception
     */
    public function __construct( DB $db, Entity|string $entity )
    {
        parent::__construct( $db );
        
        $this->entity = $entity;
        $this->table  = $entity::getTable();
    }

    public function create( Value|Entity $current, Value|Entity $parent = null, string $field = null ): void
    {
        $values  = $current->toArray( true);
        $columns = $current::fields( Obj::filterExceptType( 'scalar' ) ) ;
        $columns = array_filter(
            $columns,
            static fn( $column ) => array_key_exists( $column, $values )
        );

        if( ! $parent ){

            $this->db
                ->insert( $this->table )
                ->values( $values )
                ->columns( $columns )
                ->run();

            $current->id = $this->db->query( 'SELECT last_insert_id() as id' )->fetch()[ 'id' ];

            $this->createChildrenEntities( $current );
            $this->createChildrenObjects( $current );

            return;
        }

        if( $current::isEntity() ){

            $this->db
                ->insert( $current::getTable() )
                ->values( $values )
                ->columns( $columns )
                ->run();

            $current->id = $this->db->query( 'SELECT last_insert_id() as id' )->fetch()[ 'id' ];

            $this->db
                ->insert( $parent::getTable() . RelationSchemaProvider::RELATION_MARK . $current::getTable() )
                ->values( [ $parent->getId(), $current->getId() ] )
                ->columns( [ $parent::getTable(), $current::getTable() ] )
                ->run();

            $this->createChildrenEntities( $current );
            $this->createChildrenObjects( $current );
        }

        if( $current::isValueObject() ){

            $values[ $parent::getTable() . '_id' ] = $parent->getId();
            $columns[] = $parent::getTable() . '_id';

            $this->db
                ->insert( $parent::getTable() . '__' . $current::getTable() )
                ->columns( $columns )
                ->values( $values )
                ->run();
        }
    }

    private function createChildrenEntities( Entity $entity ): void
    {
        $rules = $entity::rules( Obj::filterExceptType('entity') );

        foreach( $rules as $rule ){
            $rule->isMultiple()
                ? array_map( fn( $child ) => $this->create( $child, $entity, $rule->field ), $entity->{$rule->field} ?? [] )
                : $this->create( $entity->{$rule->field}, $entity, $rule->field );
        }

    }

    private function createChildrenObjects( Value|Entity $entity ): void
    {
        $rules = $entity::rules( Obj::filterExceptType('object') );

        foreach( $rules as $rule ){
            $rule->isMultiple()
                ? array_map( fn( $child ) => $this->create( $child, $entity, $rule->field ), $entity->{$rule->field} ?? [] )
                : $this->create( $entity->{$rule->field}, $entity, $rule->field );
        }
    }
    
    /**
     * Saves fully valid entity to the DB
     *
     * @param array|Entity|Value $entities
     *
     * @return int[]|string[]
     * @throws \Exception
     */
    public function save( array|Value|Entity $entities ): void
    {
        // Recursion
        if( is_array( $entities ) ){
            $inserted_ids = [];
            foreach( $entities as $item ){
                $inserted_ids[] = $this->save( $item )[0];
            }

            return;
        }

        // Base case

        $values = array_filter( $entities->toArray(), 'is_scalar' );
        $this->db
            ->insert( $this->table )
            ->columns( array_keys( $values ) )
            ->values( $values )
            ->onDuplicateKey( 'update', $values )
            ->run();

        $objects      = $entities::rules( Obj::filterExceptType('object') );
        $sub_entities = $entities::rules( Obj::filterExceptType('entity') );

        $id = $this->db
                  ->query('SELECT last_insert_id() as id')
                  ->fetch()['id'];

        $entities->id = $id;
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

//    /**
//     * Building tree from request result
//     *
//     * @param array           $elements
//     * @param int|string|null $parent_id
//     * @param string          $key_id
//     * @param string          $key_parent
//     * @param string          $key_children
//     *
//     * @return array|mixed
//     */
//    protected function buildTree( array &$elements, int|string|null $parent_id = null, string $key_id = 'id', string $key_parent = 'parent', string $key_children = 'blocks' )
//    {
//        $branch = [];
//
//        // Root
//        if( $parent_id === null ){
//
//            $root_key = array_search( null, array_column( $elements, $key_parent, $key_id ), true );
//            $branch   = $elements[ $root_key ];
//
//            unset( $elements[ $root_key ] );
//
//            $branch[ $key_children ] = $this->buildTree( $elements, $branch[ $key_id ], $key_id, $key_parent, $key_children );
//
//            return $branch;
//        }
//
//        // Children
//        foreach( array_column( $elements, $key_parent, $key_id ) as $id => $parent ){
//
//            if( $parent === $parent_id ){
//
//                $branch[ $id ] = $elements[ $id ];
//
//                unset( $elements[ $id ] );
//
//                $branch[ $id ][ $key_children ] = $this->buildTree( $elements, $id, $key_id, $key_parent, $key_children );
//            }
//        }
//
//        return $branch;
//    }

//    private function appendSubEntityToData( Select $request, string $core_table, string|EntityObject $sub_entity  ): void
//    {
//        // Recursive
//        if( $sub_entity === $this->entity ){
//            // Rewrite request if we have recursive relation
//            $core_table = 'cte';
//            $request    = $this->db
//                ->select( $core_table )
//                ->with(
//                    $this->db
//                        ->cte( $core_table )
//                        ->anchor(
//                            $this->db
//                                ->select( $this->table )
//                                ->where( $condition )
//                        )
//                        ->recursive(
//                            $this->db
//                                ->select( [ $this->table, $core_table ] )
//                                ->columns( '*', $this->table )
//                                ->where( [ [ [ $this->table, 'parent' ], '=', [ $core_table, 'id' ] ] ] )
//                        )
//                )
//                ->limit( $amount, $offset );
//
//            return;
//        }
//    }

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