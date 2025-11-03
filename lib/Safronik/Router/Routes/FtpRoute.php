<?php

namespace Safronik\Router\Routes;

use Safronik\Router\Exceptions\RouterException;
use Safronik\Router\Request;

final class FtpRoute extends Route
{
    /**
     * @throws RouterException
     */
    public function searchForAvailableEndpoint(): void
    {
        $this->ensureIsAvailable();
    }
}