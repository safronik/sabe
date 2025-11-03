<?php

namespace Safronik\Controllers\Api\Rest;

use Exception;
use ReflectionException;
use Safronik\Controllers\Exceptions\EndpointNotImplementedException;
use Safronik\CodePatterns\Exceptions\ContainerException;
use Safronik\Controllers\Api\ApiController;
use Safronik\Controllers\Api\Rest\Requests\GetMethodRequest;
use Safronik\Controllers\Api\Rest\Requests\PostMethodRequest;
use Safronik\Controllers\Api\Rest\Requests\PutMethodRequest;
use Safronik\Controllers\Api\Rest\Requests\PatchMethodRequest;
use Safronik\Controllers\Api\Rest\Requests\DeleteMethodRequest;
use Safronik\Controllers\Exceptions\ControllerException;
use Safronik\Models\Entities\Entity;
use Safronik\Models\Entities\Obj;
use Safronik\Models\Services\EntityManager;
use Safronik\Router\Routes\Route;
use Safronik\Views\Api\Rest\RestResponse;
use Safronik\Views\Responses\Response;
use Safronik\Views\ViewInterface;

abstract class RestController extends ApiController{

    public const METHOD_NAME_POST   = 'post';
    public const METHOD_NAME_GET    = 'get';
    public const METHOD_NAME_PUT    = 'put';
    public const METHOD_NAME_PATCH  = 'patch';
    public const METHOD_NAME_DELETE = 'delete';
    public const METHOD_NAME_LIST   = 'list';
    public const METHODS_NAMES = [
        self::METHOD_NAME_POST,
        self::METHOD_NAME_GET,
        self::METHOD_NAME_PUT,
        self::METHOD_NAME_PATCH,
        self::METHOD_NAME_DELETE,
        self::METHOD_NAME_LIST
    ];

    private string|Entity   $entity_class;
    private string          $entity_name;
    protected ViewInterface $view;

    /**
     * @throws Exception|ReflectionException|ContainerException
     */
    public function __construct( Route $route )
    {
        parent::__construct( $route );

        $this->entity_name  = $this->getEntityNameFromRoute();
        $appNamespace       = $this->getCurrentAppNamespace();
        $this->entity_class = $appNamespace . "\\Models\\Entities\\{$this->entity_name}";

        class_exists( $this->entity_class )
            || $this->view->renderError( new ControllerException( "Invalid entity or not implemented: $this->entity_name", 501 ) );

    }

    /**
     * @throws EndpointNotImplementedException|Exception
     */
    public function _call( string $name, array $arguments = [] )
    {
        if( $name === 'handleError' ){
            $this->handleError( $arguments[0] );
            return;
        }

        $callback_type = $this->getCallbackType( $name );
        $callback      = $this->convertCallbackName( $name, $callback_type );

        method_exists( static::class, $callback )
            || throw new EndpointNotImplementedException('Action is not implemented');

        $arguments = $this->compileArguments( $callback, $arguments );

        $this->$callback( ...$arguments );
    }

    /**
     * REST GET
     *
     * @throws ContainerException
     */
    protected function get( GetMethodRequest $request, EntityManager $em ): void
    {
        // Validate request
        $request->validateBy($this->entity_class::rules() );

        // Get entities
        $entities = $em
            ->find(
                $this->entity_class,
                $request->getParametersBy( $this->entity_class::rules() ),
                ...$request->getPaginationParameters()
            );

        // Prepare Entities to output. Convert from Entities to array
        $response = RestResponse::makeByEntities( $entities );

        // Render
        $this->view->renderResponse( $response );
    }

    /**
     * REST POST
     *
     * @throws ContainerException
     */
    protected function post( PostMethodRequest $request, EntityManager $entityManager ): void
    {
        $request->validateBy( $this->entity_class::rules() );

        /** @var Entity $entity */
        $entity = new $this->entity_class( $this->request->body );

        $entityManager->flush();

        $this->view
            ->setData( [ 'inserted_id' => $entity->getId() ] )
            ->setMessage( "$this->entity_name added" )
            ->render();
    }

    /**
     * REST PUT
     * Recreates entity with new values
     */
    protected function put( PutMethodRequest $request, EntityManager $entityManager ): void
    {
    
    }
    
    /**
     * REST PATCH
     * Updates entity
     */
    protected function patch( PatchMethodRequest $request, EntityManager $entityManager ): void
    {

    }

    /**
     * REST DELETE
     *
     * - @param-api array Entity parameters to search entity and delete
     *
     * @throws Exception
     */
    protected function delete( DeleteMethodRequest $request, EntityManager $entityManager ): void
    {
        $request->validateBy( $this->entity_class::rules( Obj::filterRequired() ) );

        $deleted_id = $entityManager
            ->delete(
                $this->entity_class,
                $this->request->parameters
            );

        $entityManager->flush();

        $this->view->renderMessage( "$this->entity_name with id $deleted_id is deleted" );
    }

    /**
     * Returns a last key from the route which is exactly the name of the entity
     *
     * @return string
     */
    private function getEntityNameFromRoute(): string
    {
        $route = $this->route->getRoute();

        return $route[ array_key_last( $route ) ];
    }

    /**
     * @throws Exception
     */
    private function getCallbackType( $name ): string
    {
        return match(preg_replace( '@^([a-z]+).+@', '$1', $name )){
            'action' => 'action',
            'method' => 'method',
            default => throw new EndpointNotImplementedException('Callback type is invalid')
        };
    }
    
    private function convertCallbackName( $name, $type ): string
    {
        return strtolower( str_replace( $type, '', $name ) );
    }

    protected function getEndpoints( ?callable $filter = null ): array
    {
        return parent::getEndpoints() +
               parent::getEndpoints(
                   static fn( $method ) => in_array(
                       $method->getName(),
                       self::METHODS_NAMES,
                       true
                   )
               );
    }

    public function handleError( Exception $exception ): void
    {
        // Gather all endpoint available, if endpoint isn't implemented
        if( $exception instanceof EndpointNotImplementedException ){

            $this->view->renderResponse(
                Response::makeByException(
                    $exception,
                    [ 'available_actions' => $this->getEndpoints() ]
                )
            );

            return;
        }

        // Default exception handling
        parent::handleError( $exception );
    }
}