<?php

namespace Safronik\views;

class base{
    
    private string $message;
    private string $type;
    
    public function __construct( string $message )
    {
        $this->message = $message;
        $this->type    = 'error';
        
        $this->render();
    }
    
    private function render()
    {
        echo '<div style="background: black; color: white; padding: 10px; border-radius: 5px;">';
        echo $this->message;
        echo '</div>';
    }
}