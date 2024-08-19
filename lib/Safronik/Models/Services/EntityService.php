<?php


namespace Safronik\Models\Services;

use Safronik\CodePatterns\Structural\DI;
use Safronik\DB\DB;
use Safronik\Models\Entities\EntityObject;
use Safronik\Models\Repositories\EntityRepository;
use Safronik\Models\Services\Extensions\Pagination;

abstract class EntityService extends BaseService{
    
    use Pagination;
    
    /** @var EntityRepository */
    protected $repo;
    
    public function __construct()
    {
        $this->repo = ( new EntityRepository( DI::get( DB::class ) , static::$entity ) );
    }
    
    /**
     * @param $condition
     *
     * @return array|\Safronik\Models\Entities\EntityObject
     * @throws \Exception
     */
    public function find( $condition = [] ): array|\Safronik\Models\Entities\EntityObject
    {
        return $this->repo->read( $condition );
    }
    
    /**
     * @param ...$data
     *
     * @return int|string|array
     * @throws \Exception
     */
    public function new( ...$data ): array
    {
        $entities = $this->repo->create( $data, true );
        var_dump( $entities);
        return $this->repo->save( $entities );
    }
    
    /**
     * @param $condition
     *
     * @return int
     * @throws \Exception
     */
    public static function remove( $condition ): int
    {
        return ( new EntityRepository( DB::getInstance(), static::$entity ) )
            ->delete( $condition );
    }
    
    /**
     * Returns entities considering pagination
     *
     * @param int      $page
     * @param int|null $amount
     * @param int|null $offset
     * @param array    $condition
     *
     * @return EntityObject[]
     * @throws \Exception
     */
    public function getByPage( int $page = 1, ?int $amount = null, ?int $offset = null, array $condition = [] ): array
    {
        $amount && $this->setAmount( $amount );
        $offset && $this->setOffset( $offset );
        
        $this->calculatePagination( $page );
        
        return $this->repo->read(
            $condition,
            $this->getAmount(),
            $this->getOffset(),
        );
    }

}