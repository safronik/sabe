<?php

namespace Safronik\Views\Api;

use Safronik\Models\Entities\EntityObject;

class ApiResponse{
    
    public bool   $error       = false;
    public string $message     = '';
    public array  $m2m_message = [];
    public int    $data_count  = 0;
    public array  $data        = [];
    
    public function setError( bool $error ): void
    {
        $this->error = $error;
    }
    
    public function setMessage( string $message ): void
    {
        $this->message = $message;
    }
    
    public function setM2mMessage( ?array $m2m_message = [] ): void
    {
        $this->m2m_message = $m2m_message ?? [];
    }
    
    public function setData( mixed $data ): void
    {
        $data = is_array( $data ) ? $data : [ $data ];
        
        foreach( $data as &$datum ){
            if( $datum instanceof EntityObject ){
                $datum = $datum->toArray();
            }
        }
        
        $this->data       = $data;
        $this->data_count = count( $data );
    }
}