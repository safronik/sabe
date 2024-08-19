<?php

namespace Safronik\Controllers\Api\Rest;

use Safronik\CodePatterns\Structural\DI;
use Safronik\Controllers\Api\ApiController;
use Safronik\Controllers\Exceptions\ControllerException;
use Safronik\Helpers\SanitizerHelper;
use Safronik\Helpers\ValidationHelper;
use Safronik\Models\Entities\EntityObject;
use Safronik\Models\Services\Service;
use Safronik\Views\Api\Rest\RestView;

abstract class RestController extends ApiController{
    
    private string $entity_name;
    /** @var EntityObject  */
    private string $entity_class;
    /** @var Service  */
    private string $service_class;
    
    protected function init(): void
    {
        $this->entity_name   = $this->getEntityNameFromRoute();
        $this->entity_class  = "Models\\Entities\\{$this->entity_name}";
        $this->service_class = "Models\\Services\\{$this->entity_name}Service";
        
        class_exists( $this->entity_class )
            || DI::get( RestView::class)->outputError( new ControllerException( "Invalid entity or not implemented: $this->entity_name", 501 ) );
        
        class_exists( $this->service_class )
            || DI::get( RestView::class)->outputError( new ControllerException( "Invalid entity model or not implemented: $this->entity_name", 501 ) );
        
        parent::init();
    }
    
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
     * Create
     *
     * @return void
     * @throws \Safronik\CodePatterns\Exceptions\ContainerException
     */
    protected function post()
    {
        $rules = $this->entity_class::getRulesWithout('id');
        
        ValidationHelper::validate( $this->request->body, $rules );
        SanitizerHelper::sanitize( $this->request->body, $rules );
        
        DI::get( RestView::class )->outputSuccess(
                [ 'inserted_id' => DI::get( $this->service_class )->new( $this->request->body )[0] ],
                "$this->entity_name added"
            );
    }
    
    /**
     * Read
     *
     * @return void
     * @throws \Safronik\CodePatterns\Exceptions\ContainerException
     */
    protected function get(): void
    {
        $rules = $this->entity_class::getRulesWithoutRequired();
        
        ValidationHelper::         validate( $this->request->parameters, $rules );
        ValidationHelper::validateRedundant( $this->request->parameters, $rules );
        
        $comment = DI::get( $this->service_class )
                     ->find( $this->request->parameters )[0];
        
        DI::get( RestView::class )
          ->outputSuccess( $comment );
    }
    
    /**
     * Update
     * Recreate
     *
     * @return void
     */
    protected function put(): void
    {
    
    }
    
    /**
     * Update
     *
     * @return void
     */
    protected function patch(): void
    {
    
    }
    
    /**
     * Delete
     *
     * @return void
     * @throws \Safronik\CodePatterns\Exceptions\ContainerException
     */
    protected function delete(): void
    {
        $rules = $this->entity_class::getRulesWithoutRequired();
        
        ValidationHelper::validate( $this->request->parameters, $rules );
        
        $this->service_class::remove( $this->request->parameters );
        
        DI::get( RestView::class )
          ->outputSuccess(
              [],
              "$this->entity_name with id {$this->request->parameters['id']} is deleted"
          );
    }
    
    public function list(): void
    {
        ValidationHelper::validate( $this->request->parameters, $this->entity_class::getRulesWithoutRequired() );
        ValidationHelper::validate( $this->request->parameters,
            [
                    'page'   => [ 'required', 'type' => 'integer' ],
                    'offset' => [             'type' => 'integer' ],
                    'amount' => [             'type' => 'integer' ],
                ]
        );
        
        $pagination = array_filter( $this->request->parameters, static fn( $key) => in_array( $key, ['page','offset','amount'] ), ARRAY_FILTER_USE_KEY);
        $condition  = array_diff_assoc( $this->request->parameters, $pagination );
        
        ValidationHelper::validateRedundant( $condition, $this->entity_class::getRulesWithoutRequired() );
        
        $list = DI::get( $this->service_class )
                  ->getByPage( ...$pagination, condition: $condition );
        
        DI::get( RestView::class )->outputSuccess( $list );
    }

    
    public function __call( string $name, array $arguments )
    {
        $callback_type = $this->getCallbackType( $name );
        $callback      = $this->convertCallbackName( $name, $callback_type );
        
        method_exists( static::class, $callback )
            || DI::get( RestView::class )->outputError(
                    new \Exception('Action is not implemented', 501 ),
                    $this->getAvailableActions()
                );
        
        $this->callCallback( $callback );
    }
    
    private function getCallbackType( $name ): string
    {
        return preg_replace( '@^([a-z]+).+@', '$1', $name );
    }
    
    private function convertCallbackName( $name, $type ): string
    {
        return strtolower( str_replace( $type, '', $name ) );
    }
    
    private function callCallback( $callback )
    {
        try{
            $this->checkApiKey();
            // $this->controlLimits( $period, $limit, [
            //     'controller' => static::class,
            //     'method'     => $name,
            // ]);
           $this->$callback();
        }catch( \Exception $exception ){
            DI::get( RestView::class )->outputError( $exception );
        }
    }
}