<?php

namespace Safronik\Services\Event;

use Safronik\Core\CodeTemplates\Service;
use Safronik\Core\CodeTemplates\Singleton;
use Safronik\Services\Serviceable;

class Event implements Serviceable
{
    use Service, Singleton;
    
    protected static string $service_alias = 'event';
    
    private array $events = [];
    private array $allowed_types = [ 'on', 'after', 'filter_input', 'filter_out', 'custom' ];
    
    public static function register( $event ): void
    {
        $self                   = self::getInstance();
        $self->events[ $event ] = [];
    }
    
    public static function filterInput( $event, callable $callback ): void
    {
        self::getInstance()->createEvent( 'filter_input', $event, $callback );
    }
    
    public static function before( $event, callable $callback ): void
    {
        self::getInstance()->createEvent( 'on', $event, $callback );
    }
    
    public static function after( $event, callable $callback ): void
    {
        self::getInstance()->createEvent( 'after', $event, $callback );
    }
    
    public static function filterOut( $event, callable $callback ): void
    {
        self::getInstance()->createEvent( 'filter_out', $event, $callback );
    }
    
    private function createEvent( $type, $event, callable $callback )
    {
        $this->events[ $event ][ $type ] = [
            'callback' => $callback,
        ];
    }
    
    public static function triggerFilterInput( $event, $arguments ): mixed
    {
        return self::getInstance()->isEventExists( 'filter_input', $event )
            ? self::getInstance()->trigger( 'filter_input', $event, ...$arguments )
            : $arguments;
    }
    
    public static function triggerBefore( $event ): void
    {
        if( self::getInstance()->isEventExists( 'on', $event ) ){
            self::getInstance()->trigger( 'on', $event );
        }
    }
    
    public static function triggerAfter( $event ): void
    {
        if( self::getInstance()->isEventExists( 'after', $event ) ){
            self::getInstance()->trigger( 'after', $event );
        }
    }
    
    public static function triggerFilterOutput( $event, $return_value ): mixed
    {
        return self::getInstance()->isEventExists( 'filter_out', $event )
            ? self::getInstance()->trigger( 'filter_out', $event, $return_value )
            : $return_value;
    }
    
    public static function triggerCustom( $event ): void
    {
        if( self::getInstance()->isEventExists( 'custom', $event ) ){
            self::getInstance()->trigger( 'custom', $event );
        }
    }
    
    private function isEventExists( string $type, $event ): bool
    {
        return isset( $this->events[ $event ][ $type ] );
    }
    
    private function trigger( string $type, string $event, ...$params ): mixed
    {
        return $this->events[ $event ][ $type ]['callback']( ...$params );
    }
    
}