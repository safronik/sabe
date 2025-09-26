<?php

namespace Safronik\Models\Entities;

use Safronik\Models\Services\EntityManager;

abstract class Entity extends Value{
    
    public mixed $id;

    protected const RULES = [
        'id' => [ 'required',  'type' => 'integer', 'length' => 11, 'content' => '@^[0-9]+$@', 'extra' => 'AUTO_INCREMENT' ],
    ];

    protected static function createRules(): void
    {
        $rules = self::RULES;
        array_walk(
            $rules,
            static function( &$rule, $name ){
                $rule = new Rule( $rule, $name );
            }
        );
        static::$rules = array_merge( static::$rules, $rules );

        parent::createRules();
    }

    public function __construct( $data = [], $is_child = false )
    {
        parent::__construct( $data );

        $is_child || EntityManager::setEntityStateAs( EntityManager::NEW, $this );
    }

    public function getId()
    {
        return $this->id ?? null;
    }

    public function __destruct()
    {
        EntityManager::setEntityStateAs( EntityManager::TO_DELETE, $this );
    }
}