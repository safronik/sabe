<?php

namespace Safronik\Router\Routes;

use Safronik\Router\Exceptions\RouterException;

final class ApiRoute extends AbstractRoute
{
    /**
     * @throws RouterException
     */
    public function searchForAvailableEndpoint(): void
    {
        try{
            $this->ensureIsAvailable();
        }catch( RouterException $exception ){
            $this
                ->setEndpoint('ShowEndpoints')
                ->ensureIsAvailable();
        }
    }
}