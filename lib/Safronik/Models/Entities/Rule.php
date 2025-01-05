<?php

namespace Safronik\Models\Entities;

use Safronik\Core\SanitizerHelper;
use Safronik\Core\ValidationHelper;

class Rule{

    public readonly string              $field;
    public readonly bool                $required;
    public readonly Entity|Value|string $type;
    public readonly int|string|array    $length;
    public readonly array|string        $content;
    public readonly string              $extra;
    public readonly mixed               $default;

    public ?string $table = null;
    
    public readonly bool $is_scalar;
    public readonly bool $is_entity;
    public readonly bool $is_object;
    public readonly bool $is_multiple;

    public readonly array $initial;

    private array $validation_rules = [
        'type'    => [ 'type' => 'string', 'required', ],
        'length'  => [ 'type' => 'integer|array', 'default' => 1, ],
        'content' => [ 'type' => 'string|array', ],
        'extra'   => [ 'type' => 'string', 'content' => ['AUTO_INCREMENT', 'UNIQUE'] ],
        'default' => [ 'type' => 'integer|string', ],
    ];

    public function __construct( array $rule_parameters, string $name )
    {
        // Check that the rule is correct
        ValidationHelper::validate( $rule_parameters, $this->validation_rules );
        SanitizerHelper::sanitize( $rule_parameters, $this->validation_rules );

        $this->initial  = $rule_parameters;
        $this->field    = $name;
        $this->required = (bool)count(
            array_intersect(
                [ '!', 'required' ],
                array_filter($rule_parameters, static fn($item) => ! is_array( $item ) )
            )
        );
        
        // Type
        $this->type = $rule_parameters['type'];
        $this->is_scalar = in_array( $this->type, [ 'string', 'integer', 'float', 'bool', 'array' ] );
        $this->is_entity = class_exists( $this->type ) && is_subclass_of( $this->type, Entity::class );
        $this->is_object = class_exists( $this->type ) && is_subclass_of( $this->type, Value::class ) && ! is_subclass_of( $this->type, Entity::class );
        
        // Length
        $this->length = is_array($rule_parameters['length'] )
            ? $rule_parameters['length'][1] ?? $rule_parameters['length'][0]
            : $rule_parameters['length'];
        $this->is_multiple = ( $this->is_object || $this->is_entity ) &&
                             ( $this->length > 1 || count( array_intersect( [ '[]', 'array', 'multiple', 'collection' ], $rule_parameters ) ) );
        
        $this->content = $rule_parameters['content'] ?? '';
        $this->default = $rule_parameters['default'] ?? '';
        $this->extra   = $rule_parameters['extra']   ?? '';
        
    }

    public function isScalar(): bool
    {
        return $this->is_scalar;
    }
    
    public function isEntity(): bool
    {
        return $this->is_entity;
    }

    public function isObject(): bool
    {
        return $this->is_object;
    }
    
    public function isMultiple(): bool
    {
        return $this->is_multiple;
    }
}