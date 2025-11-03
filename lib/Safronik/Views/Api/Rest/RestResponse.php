<?php

namespace Safronik\Views\Api\Rest;

use Safronik\Models\Entities\Entity;
use Safronik\Views\Api\ApiResponse;

/** Solves a problem of conservation Entities to array */
class RestResponse extends ApiResponse{

    /**
     * Static constructor
     *
     * @param Entity[] $entities
     */
    public static function makeByEntities( array $entities ): self
    {
        $response = new self;
        $response->setData( $entities );

        return $response;
    }

    /**
     * Converts data
     */
    public function setData( mixed $data ): static
    {
        $data = is_array( $data ) ? $data : [ $data ];
        
        foreach( $data as &$datum ){
            if( $datum instanceof Entity ){
                $datum = $datum->toArray();
            }
        } unset( $datum );

        $this->data['data']  = $data;
        $this->data['count'] = count( $data );

        return $this;
    }
}