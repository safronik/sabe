<?php

// @todo Should be a code template, not a service
/** @todo inplement priority groups
 *  0 - 100   - crutches and reserve
 *  100 - 199 - security
 *  200 - 299 - base logic (default)
 */

namespace Safronik\Services\Event;

use Safronik\Core\Exceptions\EventException;

use Safronik\Core\CodeTemplates\Interfaces\Serviceable;
use Safronik\Core\CodeTemplates\Service;
use Safronik\Core\CodeTemplates\Singleton;

class Event implements Serviceable
{
    use Service, Singleton;
    
    protected static string $service_alias = 'event';
    
    private array $events = [];
    private array $allowed_types = [
        'before',
        'after',
        'filter_input',
        'filter_output',
    ];
    
    public static function hook( $type, $event, callable $callback ): void
    {
        self::getInstance()->create( $type, $event, $callback );
    }
    
    public static function filterInput( $event, callable $callback ): void
    {
        self::getInstance()->create( 'filter_input', $event, $callback );
    }
    
    public static function before( $event, callable $callback ): void
    {
        self::getInstance()->create( 'before', $event, $callback );
    }
    
    public static function after( $event, callable $callback ): void
    {
        self::getInstance()->create( 'after', $event, $callback );
    }
    
    public static function filterOutput( $event, callable $callback ): void
    {
        self::getInstance()->create( 'filter_output', $event, $callback );
    }
    
    private function create( $type, $event, callable $callback, int $priority = 200 )
    {
        ! in_array( $type, $this->allowed_types, true)
            && throw new EventException('Event type is not valid: ' . $type );
        
        $this->events[ $event ][ $type ][ $priority ] = $callback;
    }
    
    public static function triggerFilterInput( $event, $arguments ): mixed
    {
        return self::getInstance()->exists( 'filter_input', $event )
            ? self::getInstance()->trigger( 'filter_input', $event, ...$arguments )
            : $arguments;
    }
    
    public static function triggerBefore( $event ): void
    {
        if( self::getInstance()->exists( 'before', $event ) ){
            self::getInstance()->trigger( 'before', $event );
        }
    }
    
    public static function triggerAfter( $event ): void
    {
        if( self::getInstance()->exists( 'after', $event ) ){
            self::getInstance()->trigger( 'after', $event );
        }
    }
    
    public static function triggerFilterOutput( $event, $return_value ): mixed
    {
        return self::getInstance()->exists( 'filter_output', $event )
            ? self::getInstance()->trigger( 'filter_output', $event, $return_value )
            : $return_value;
    }
    
    public static function triggerCustom( $event ): void
    {
        if( self::getInstance()->exists( 'custom', $event ) ){
            self::getInstance()->trigger( 'custom', $event );
        }
    }
    
    private function exists( string $type, $event ): bool
    {
        return isset( $this->events[ $event ][ $type ] );
    }
    
    private function trigger( string $type, string $event, ...$params ): mixed
    {
        // return current(
        //     array_reduce(
        //         $this->events[ $event ][ $type ],
        //         function( $carry, ...$params ){
        //
        //         },
        //         ...$params
        //     )
        // );
        //
        // foreach( $this->events[ $event ][ $type ] as $callback ){
        //     $callback( ...$params );
        // }
        
        return $this->events[ $event ][ $type ]( ...$params );
    }
    
}