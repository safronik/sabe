<?php

namespace Safronik\Models\Repositories\EntityRepository;

use Safronik\Models\Entities\Entity;
use Safronik\Models\Entities\Obj;
use Safronik\Models\Entities\Value;

trait EntityRepositoryReader
{
    /**
     * Returns an array of entities or single entity
     *
     * @param array    $condition
     * @param int|null $amount
     * @param int|null $offset
     *
     * @return Entity[]
     * @throws \Exception
     */
    public function read( array $condition = [], ?int $amount = null, ?int $offset = null ): array
    {
        $data = $this->db
            ->select( $this->table )
            ->where( $condition )
            ->limit( $amount, $offset )
            ->run();

        $data && $this->appendObjectsToData ( $data, $this->entity::rules( Obj::filterExceptType('object') ), $this->table );
        $data && $this->appendEntitiesToData( $data, $this->entity::rules( Obj::filterExceptType('entity') ), $this->table );

        foreach( $data as $entity_datum ){
            $entities[] = new $this->entity( $entity_datum );
        }

        return $entities ?? [];
    }

    protected function appendObjectsToData( array &$data, array $rules, string $table ): void
    {
        foreach( $rules as $value_object_rule ){
            $this->appendObjectToData(
                $data,
                $table,
                $value_object_rule->type,
                $value_object_rule->field,
                $value_object_rule->isMultiple()
            );
        }
    }

    protected function appendEntitiesToData( array &$data, array $rules, string $table ): void
    {
        foreach( $rules as $rule ){
            $this->appendEntityToData(
                $data,
                $table,
                $rule->type,
                $rule->field,
                $rule->isMultiple()
            );
        }
    }

    /**
     * @param array $data
     * @param string $parent_table
     * @param string|Value $value_object_type
     * @param string $parent_field
     * @param bool $is_multiple
     * @return void
     */
    protected function appendObjectToData( array &$data, string $parent_table, string|Value $value_object_type, string $parent_field, bool $is_multiple ): void
    {
        $parent_ids   = array_column( $data, 'id' );

        if( ! $parent_ids ){
            return;
        }

        $objects_data = $this->readObjectsData( $parent_table, $parent_ids, $value_object_type );

        $objects_data && $this->appendObjectsToData( $objects_data, $value_object_type::rules( Obj::filterExceptType('object') ), $value_object_type::getTable() );

        foreach( $objects_data as &$object_datum ){

            $parent_key = array_search( $object_datum[ $parent_table . '_id' ], $parent_ids, true );

            $this->filterObjectData( $object_datum );

            if( $is_multiple ){
                $data[ $parent_key ][ $parent_field ][] = $object_datum;
            }else{
                $data[ $parent_key ][ $parent_field ] = $object_datum;
            }
        }
    }

    protected function appendEntityToData( array &$data, string $parent_table, string|Entity $entity_type, string $parent_field, bool $is_multiple ): void
    {
        $parent_ids  = array_column( $data, 'id' );

        if( ! $parent_ids ){
            return;
        }

        $entity_data = $is_multiple
            ? $this->readEntitiesData( $parent_table, $parent_ids, $entity_type )
            : $this->readEntityData( $parent_table, $parent_ids, $entity_type );

        $entity_data && $this->appendObjectsToData( $entity_data, $entity_type::rules( Obj::filterExceptType('object') ), $entity_type::getTable() );
        $entity_data && $this->appendEntitiesToData( $entity_data, $entity_type::rules( Obj::filterExceptType('entity') ), $entity_type::getTable() );

        // Process existing data
        foreach( $entity_data as &$entity_datum ){

            $parent_key = array_search( $entity_datum[ $parent_table . '.id' ], $parent_ids, true );

            $this->filterEntityData( $entity_datum );

            if( $is_multiple ){
                $data[ $parent_key ][ $parent_field ][] = $entity_datum;
            }else{
                $data[ $parent_key ][ $parent_field ] = $entity_datum;
            }
        } unset( $entity_datum );

        // Set missing data to null or empty array
        foreach( $data as &$datum ){
            $datum[ $parent_field ] ??= $is_multiple ? [] : null;
        }
    }

    protected function readObjectsData( string $parent_table, array $parent_ids, string|Value $value_object_type ): array
    {
        return $this->db
            ->select( $parent_table . '__' . $value_object_type::getTable() )
            ->where( [ [ $parent_table . '_id', 'in', $parent_ids ] ] )
            ->run();
    }

    protected function readEntitiesData( string $parent_table, array $parent_ids, string|Entity $entity_type ): array
    {
        $sub_entity_table = $entity_type::getTable();
        $relation_table   = $parent_table . '__to__' . $sub_entity_table;

        return $this->db
            ->select( $sub_entity_table )
            ->join(   [ [ $relation_table, $sub_entity_table .'_id' ], '=', [ $sub_entity_table, 'id' ] ], 'inner', false )
            ->join(   [ [ $parent_table, 'id' ], '=', [ $relation_table, $parent_table .'_id' ] ], 'inner', [ 'id' ] )
            ->where( [ [ [ $parent_table, 'id' ], 'in', $parent_ids ] ] )
            ->run();
    }

    protected function readEntityData( string $parent_table, array $parent_ids, string|Entity $entity_type ): array
    {
        $sub_entity_table = $entity_type::getTable();

        return $this->db
            ->select( $sub_entity_table )
            ->join(   [ [ $parent_table, $sub_entity_table . '_id' ], '=', [ $sub_entity_table, 'id' ] ], 'inner', [ 'id' ] )
            ->where( [ [ [ $parent_table, 'id' ], 'in', $parent_ids ] ] )
            ->run();
    }

    protected function filterObjectData( array &$datum )
    {
        foreach( $datum as $key => $item ){
            if( str_ends_with($key, '_id' ) ){
                unset( $datum[ $key ] );
            }
        }
    }

    protected function filterEntityData( array &$datum )
    {
        foreach( $datum as $key => $item ){
            if( str_ends_with($key, '.id' ) || str_ends_with($key, '_id' )){
                unset( $datum[ $key ] );
            }
            if( $key === 'parent' ){
                unset( $datum[ $key ] );
            }
        }
    }
}