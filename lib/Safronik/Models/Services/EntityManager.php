<?php


namespace Safronik\Models\Services;

use Exception;
use Safronik\CodePatterns\Exceptions\ContainerException;
use Safronik\CodePatterns\Generative\Singleton;
use Safronik\CodePatterns\Structural\DI;
use Safronik\Models\Entities\Entity;
use Safronik\Models\EntityManager\ObjectsTree;
use Safronik\Models\Repositories\EntityRepository;
use Safronik\Models\Services\Extensions\Pagination;

final class EntityManager{

    use Singleton;
    use Pagination;

    /** @var Entity[][] */
    protected $entityMap = [];

    protected $tree;

    /** @var Entity[][] */
    protected array $entitiesByStates = [
        self::STEADY    => [],
        self::NEW       => [],
        self::TO_UPDATE => [],
        self::TO_DELETE => [],
    ];

    public const NEW       = 'create';
    public const STEADY    = 'steady';
    public const TO_UPDATE = 'update';
    public const TO_DELETE = 'delete';

    private array $repositories = [];
    private array $requests_cache = [];

    public function __construct()
    {
        $this->tree = new ObjectsTree();
    }

    /**
     * @param Entity[] $create
     * @return void
     * @throws ContainerException
     */
    private function create( array $create ): void
    {
        $create = array_reverse( $create );
        foreach( $create as $entity ){
            $this->getRepo( $entity )->create( $entity );

            unset( $this->entityMap[ $entity::class ][ spl_object_id( $entity ) ] );
            $this->entityMap[ $entity::class ][ $entity->getId() ] = $entity;
        }
    }

    /**
     * @param string $classname
     * @param array $condition
     * @param int $page
     * @param int|null $amount
     * @param int|null $offset
     *
     * @return Entity[]
     *
     * @throws ContainerException
     * @throws Exception
     */
    public function find( string $classname, array $condition = [], int $page = 1, ?int $amount = null, ?int $offset = null ): array
    {
        $amount && $this->setAmount( $amount );
        $offset && $this->setOffset( $offset );

        $this->calculatePagination( $page );

        $hash       = $this->calculateConditionHash( $condition, $amount, $offset );
        $cached_ids = $this->getCachedIds( $classname, $hash );
        $entities   = $cached_ids
            ? array_intersect_key( $this->entityMap[ $classname ], array_flip( $cached_ids ) ) // Get from cache
            : $this
                ->getRepo( $classname )
                ->read( $condition, $amount, $offset );     // Database request

        $cached_ids
            || $this->cacheRequest( $classname, $hash, $entities );

        return $entities;
    }

    /**
     * @param Entity[] $update
     * @return void
     * @throws ContainerException
     * @throws Exception
     */
    private function save( array $update ): void
    {
        foreach( $update as $entity ){
            $this->getRepo( $entity )->save( $entity );
        }
    }

    /**
     * @param Entity[] $delete
     * @return void
     * @throws ContainerException
     * @throws Exception
     */
    private function remove( array $delete ): void
    {
        foreach( $delete as $entity ){
            $this->getRepo( $entity )->delete( $entity->toArray() );
            unset( $this->entityMap[ $entity::class ][ $entity->getId() ] );
        }
    }

    public function delete( string $entity_class, $condition ): int|string
    {
        $entity = $this->find( $entity_class, $condition, amount: 1 )[ 0 ] ?? null;
        $entity || throw new Exception('Entity not found', 404 );

        $deleted_id = $entity->getId();

        unset( $entity );

        return $deleted_id;
    }

    public static function setEntityStateAs( string $state, Entity $entity ): void
    {
        self::getInstance()->entityMap[ $entity::class ][ $entity->getId() ?: spl_object_id( $entity ) ] = $entity;
        self::getInstance()->entitiesByStates[ $state ][] = $entity;
    }

    /**
     * @throws ContainerException
     */
    public function flush(): void
    {
        $this->entitiesByStates[ self::NEW ] &&
            $this->create( $this->entitiesByStates[ self::NEW ] );
        $this->entitiesByStates[ self::STEADY ] = $this->entitiesByStates[ self::NEW ];
        $this->entitiesByStates[ self::NEW ]    = [];

        $this->entitiesByStates[ self::TO_UPDATE ] &&
            $this->save( $this->entitiesByStates[ self::TO_UPDATE ] );
        $this->entitiesByStates[ self::STEADY ]    = $this->entitiesByStates[ self::TO_UPDATE ];
        $this->entitiesByStates[ self::TO_UPDATE ] = [];

        $this->entitiesByStates[ self::TO_DELETE ] &&
            $this->delete( $this->entitiesByStates[ self::TO_DELETE ] );
        $this->entitiesByStates[ self::TO_DELETE ] = [];
    }

    /**
     * @param Entity $entity
     * @return EntityRepository
     * @throws ContainerException
     * @throws Exception
     */
    private function getRepo( string|Entity $entity ): EntityRepository
    {
        $classname = is_string( $entity ) ? $entity : $entity::class;

        return $this->repositories[ $classname ]
            ??= DI::get(
                EntityRepository::class,
                [ 'entity' => $classname ]
            );
    }

    private function calculateConditionHash( array $condition, ?int $amount, ?int $offset ): string
    {
        return hash( 'md5', implode( '', $condition ) . $amount . $offset );
    }

    private function getCachedIds( string $classname, string $hash ): array
    {
        return $this->requests_cache[ $classname ][ $hash ] ?? [];
    }

    private function cacheRequest( string $classname, string $hash, array $entities ): void
    {
        $this->requests_cache[ $classname ][ $hash ] = array_map( static fn( $entity) => $entity->getId(), $entities );
    }
}