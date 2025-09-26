<?php

namespace Safronik\Controllers\Middlewares\Api\CallLimit;

interface CallLimitGatewayInterface{
    
    /**
     * Gets interval by ID
     *
     * @param string $id
     *
     * @return object|null
     * @throws \Exception
     */
    public function getIntervalById( string $id ): ?object;
    
    /**
     * Deletes interval
     *
     * @param object $interval
     *
     * @return void
     * @throws \Exception
     */
    public function dropInterval( object $interval ): void;
    
    /**
     * Updates or creates interval
     *
     * @param object|null $interval
     *
     * @return void
     */
    public function updateInterval( object $interval ): void;
}