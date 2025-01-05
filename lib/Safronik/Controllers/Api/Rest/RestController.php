<?php

namespace Safronik\Controllers\Api\Rest;

use Exception;
use Safronik\Controllers\Api\Rest\Exceptions\MethodNotImplementedException;
use Safronik\CodePatterns\Exceptions\ContainerException;
use Safronik\CodePatterns\Structural\DI;
use Safronik\Controllers\Api\ApiController;
use Safronik\Controllers\Exceptions\ControllerException;
use Safronik\Core\ValidationHelper;
use Safronik\Models\Entities\Entity;
use Safronik\Models\Entities\Obj;
use Safronik\Models\Entities\Rule;
use Safronik\Models\Services\EntityManager;
use Safronik\Router\Routes\AbstractRoute;
use Safronik\Views\Api\Rest\RestView;
use Safronik\Views\ViewInterface;

abstract class RestController extends ApiController{

    private string|Entity            $entity_class;
    private string                   $entity_name;
    private EntityManager            $entityManager;
    protected ViewInterface|RestView $view;

    public function __construct( AbstractRoute $route, RestView $view)
    {
        parent::__construct($route, $view);
    }

    /**
     * @throws Exception
     */
    public function __call( string $name, array $arguments )
    {
        if( $name === 'handleError' ){
            $this->handleError( $arguments[0] );
            return;
        }

        $callback_type = $this->getCallbackType( $name );
        $callback      = $this->convertCallbackName( $name, $callback_type );

        method_exists( static::class, $callback )
            || throw new MethodNotImplementedException('Action is not implemented', 501 );

        $this->callCallback( $callback );
    }

    /**
     * @throws ContainerException
     */
    protected function init(): void
    {
        $this->entity_name   = $this->getEntityNameFromRoute();
        $this->entity_class  = "Models\\Entities\\{$this->entity_name}";

        class_exists( $this->entity_class )
            || $this->view->renderError( new ControllerException( "Invalid entity or not implemented: $this->entity_name", 501 ) );

        $this->entityManager = DI::get( EntityManager::class );
    }

    /**
     * REST POST
     *
     * @return void
     * @throws \Safronik\CodePatterns\Exceptions\ContainerException
     */
    protected function post()
    {
        /** @var Entity $entity */
        $entity = new $this->entity_class( $this->request->body );
        $this->entityManager->flush();

        $this->view
            ->setData( [ 'inserted_id' => $entity->getId() ] )
            ->setMessage( "$this->entity_name added" )
            ->render();
    }
    
    /**
     * REST GET
     *
     * @return void
     * @throws \Safronik\CodePatterns\Exceptions\ContainerException
     */
    protected function get(): void
    {
        $paginationParameters = array_filter( $this->request->parameters, static fn($key) => in_array( $key, ['page','offset','amount'] ), ARRAY_FILTER_USE_KEY);
        $conditionParameters  = array_diff_assoc( $this->request->parameters, $paginationParameters );

        ValidationHelper::         validate( $conditionParameters, $this->entity_class::rules() );
        ValidationHelper::validateRedundant( $conditionParameters, $this->entity_class::rules() );
        ValidationHelper::         validate( $paginationParameters,
            [
                'page'   => new Rule( [ 'type' => 'integer' ], 'page' ),
                'offset' => new Rule( [ 'type' => 'integer' ], 'offset' ),
                'amount' => new Rule( [ 'type' => 'integer' ], 'amount' ),
            ]
        );

        $output = $this
            ->entityManager
                ->find(
                    $this->entity_class,
                    $conditionParameters,
                    ...$paginationParameters
                );

        $this
            ->view
                ->renderData( $output );
    }
    
    /**
     * REST PUT
     * Recreates entity with new values
     *
     * @return void
     */
    protected function put(): void
    {
    
    }
    
    /**
     * REST PATCH
     * Updates entity
     *
     * @return void
     */
    protected function patch(): void
    {

    }

    /**
     * REST DELETE
     *
     * - @param-api array Entity parameters to search entity and delete
     *
     * @return void
     * @throws Exception
     */
    protected function delete(): void
    {
        $rules = $this->entity_class::rules( Obj::filterRequired() );
        ValidationHelper::         validate( $this->request->parameters, ...$rules );
        ValidationHelper::validateRedundant( $this->request->parameters, ...$rules );

        $deleted_id = $this->entityManager->delete( $this->entity_class, $this->request->parameters );

        $this->entityManager->flush();

        $this->view->renderMessage( "$this->entity_name with id $deleted_id is deleted" );
    }

//    /**
//     * List entities
//     *
//     * @return void
//     * @throws \Exception
//     */
//    protected function list(): void
//    {
//        $this->get();
//    }

    /**
     * Returns last key from the route which is exactly the name of the entity
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
            default => throw new Exception('callback type is invalid')
        };
    }
    
    private function convertCallbackName( $name, $type ): string
    {
        return strtolower( str_replace( $type, '', $name ) );
    }

    /**
     * @throws ContainerException
     */
    private function callCallback( $callback ): void
    {
        try{
            // @todo implement middleware
//            $this->checkApiKey();
//            $this->controlLimits(
//                $period,
//                $limit,
//                [
//                    'controller' => static::class,
//                    'method'     => $name,
//                ]
//            );
            $this->$callback();
        }catch( Exception $exception ){
            DI::get( RestView::class )->renderError( $exception );
        }
    }

    /**
     * @param Exception $exception
     * @return void
     */
    public function handleError( Exception $exception ): void
    {
        $this->view->renderError(
            $exception,
            $this->getEndpoints() +
            $this->getEndpoints(
                static fn( $method ) => in_array( $method->getName(), [ 'post', 'get', 'put', 'delete', 'list' ] )
            )
        );
    }
}