<?php

namespace Safronik\Models\Entities\Extensions;

use Safronik\Models\Entities\Rule;

trait Rules
{
    protected const RULES = [];
    protected static array $rules = [];

    protected static function createRules(): void
    {
        $rules = static::RULES;

        array_walk(
            $rules,
            static function( &$rule, $name ){
                $rule = new Rule( $rule, $name );
            }
        );

        static::$rules = array_merge( static::$rules, $rules );
    }

    /**
     * @param callable ...$filters
     * @return Rule[]
     */
    public static function rules( ...$filters ): array
    {
        static::$rules || static::createRules();

        $rules = static::$rules;

        foreach( $filters as $filter ){
            $rules = array_filter( $rules, $filter, ARRAY_FILTER_USE_BOTH );
        }

        return $rules;
    }

    public static function rule( string $name ): Rule
    {
        return current( self::rules( self::filterCertainFields( $name ) ) );
    }

    public static function fields( ...$filters ): array
    {
        return array_keys( static::rules( ...$filters ) );
    }

    public static function filterExceptFields( ...$fields ): callable
    {
        return static fn( $rule, $field ) => ! in_array( $field, $fields, true );
    }

    public static function filterCertainFields( ...$fields ): callable
    {
        return static fn( $rule, $field ) => in_array( $field, $fields, true );
    }

    public static function filterRequired(): callable
    {
        return static fn( $rule, $field ) => ! $rule->required;
    }

    public static function filterOptional(): callable
    {
        return static fn( $rule, $field ) => $rule->required;
    }

    public static function filterExceptType( string $type ): callable
    {
        return match( $type ){
            'scalar'  => static fn( $rule, $field )  => $rule->is_scalar,
            'integer' => static fn( $rule, $field )  => $rule->type === 'integer',
            'string'  => static fn( $rule, $field )  => $rule->type === 'string',
            'entity'  => static fn( $rule, $field )  => $rule->is_entity,
            'object'  => static fn( $rule, $field )  => $rule->is_object,
        };
    }

    public static function hasProperty( string $property ): bool
    {
        return isset( static::$rules[ $property ] );
    }
}