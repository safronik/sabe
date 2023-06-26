<?php

namespace Safronik\Core\CodeTemplates;

trait Event
{
    public function __call( string $name, array $arguments )
    {
        $event = $name;
        $name  = '_' . $name;
        
                        \Safronik\Services\Event\Event::triggerBefore( $event );
        $arguments    = \Safronik\Services\Event\Event::triggerFilterInput( $event, $arguments );
        $return_value = $this->$name( ...$arguments );
        $return_value = \Safronik\Services\Event\Event::triggerFilterOutput( $event, $return_value );
                        \Safronik\Services\Event\Event::triggerAfter( $event );
        
        return $return_value;
    }
}