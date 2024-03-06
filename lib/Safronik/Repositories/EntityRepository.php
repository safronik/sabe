<?php

namespace Safronik\Repositories;

use Safronik\Domains\Entities\EntityObject;
use Safronik\Repositories\Interfaces\BaseRepositoryInterface;

class EntityRepository
{
    
    private BaseRepositoryInterface $gateway;
    private string                  $entity;
    
    /**
     * @param string                  $entity_classname
     * @param BaseRepositoryInterface $gateway
     *
     * @throws \Exception
     */
    public function __construct( string $entity_classname, BaseRepositoryInterface $gateway )
    {
        ! class_exists( $entity_classname )
            && throw new \Exception('BadClassName: ' . $entity_classname );
        
        $this->entity = $entity_classname;
        $this->gateway = $gateway;
        $this->gateway->setEntity( $this->entity );
    }
    
    /**
     * @param $data
     *
     * @return EntityObject|EntityObject[]
     */
    public function create( $data ): array|EntityObject
    {
        return $this->gateway->create( $data );
    }
    
    /**
     * @param array    $condition
     * @param int|null $amount
     * @param int|null $offset
     *
     * @return EntityObject|EntityObject[]
     * @throws \Exception
     */
    public function get( array $condition = [], ?int $amount = null, ?int $offset = null ): array|EntityObject
    {
        return $this->gateway->read( $condition, $amount, $offset );
    }
    
    /**
     * @param EntityObject $item
     *
     * @return bool
     */
    public function save( EntityObject $item ): bool
    {
        return $this->gateway->save( $item );
    }
    
    /**
     * @param $condition
     * @return int|null
     */
    public function delete( $condition ): ?int
    {
        return $this->gateway->delete( $condition );
    }
    
    /**
     * @param array $condition
     *
     * @return int
     */
    public function count( array $condition = [] ): int
    {
        return $this->gateway->count( $condition );
    }
}