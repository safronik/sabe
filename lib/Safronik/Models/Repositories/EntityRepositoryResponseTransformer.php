<?php

namespace Safronik\Models\Repositories;

use Safronik\Models\Entities\Rule;

class EntityRepositoryResponseTransformer
{
    private const ID_FIELD = 'id';

    /** @var Rule[] */
    private array  $rules;
    private string $table;
    private array  $data;
    private array  $transformed_data;

    public function __construct( &$data, array $rules, string $table )
    {
        $this->data  = $data;
        $this->rules = $rules;
        $this->table = $table;
    }

    public static function transform( &$data, array $rules, string $table ): array
    {
        $transformer = ( new static( $data, $rules, $table ) );
        $transformer->transformData();

        return $transformer->getTransformedData();
    }

    public function transformData()
    {
        $this->filterDataFromKey( $this->data, $this->table );
        $this->transformed_data = $this->buildTree( $this->data, $this->rules, '', null, self::ID_FIELD );
    }

    public function getTransformedData(): array
    {
        return $this->transformed_data;
    }

    private function getSubObjects( ?array $rules = null ): array
    {
        return array_filter(
            $rules ?? $this->rules,
            static fn( $rule ) => $rule->isObject() && $rule->isMultiple()
        );
    }

    private function getSubEntities( ?array $rules = null ): array
    {
        return array_filter(
            $rules ?? $this->rules,
            static fn( $rule ) => $rule->isEntity()
        );
    }

    private function filterDataFromKey( array &$data, string $key )
    {
        return array_walk(
            $data,
            static function( $datum ) use ( $key ){
                unset( $datum[ $key ] );
            }
        );
    }

    private function buildTree( array $data = null, ?array $rules = null, $prefix = '', $parent_id = null, $id_field = null )
    {
        $out = [];

        foreach($data as $datum){

            foreach( $rules as $property => $rule ){

                if( $rule->isObject() && $rule->isMultiple() ){

                    $prop_prefix      = ($prefix ?: $this->table) . '__' . $rule->type::getTable() . '.';
                    $sub_object_data = array_filter(
                        $datum,
                        static fn($key) => str_starts_with($key, $prop_prefix),
                        ARRAY_FILTER_USE_KEY
                    );
                    $out[ $datum[ $id_field ] ][ $property ][] = $this->buildTree(
                        $sub_object_data,
                        $rule->type::rules(),
                        $prop_prefix
                    );
                }else{
                    $out[ $datum[ $id_field ] ][ $property ] = $prefix
                        ? $datum[ $prefix . $property ]
                        : $datum[ $property ];
                }
            }
        }

        return $out ?? [];
    }

    private function getEntitiesFromRules()
    {
        foreach( $this->rules as $rule ){
            if( $rule->isEntity() ){
                $entities[] = $rule->type;
            }
        }
    }
}