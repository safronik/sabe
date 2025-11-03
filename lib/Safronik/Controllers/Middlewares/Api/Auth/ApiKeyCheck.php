<?php

namespace Safronik\Controllers\Middlewares\Api\Auth;

use Safronik\Core\Helpers\ValidationHelper;
use Safronik\Middleware\MiddlewareInterface;
use Safronik\Models\Entities\Rule;
use Safronik\Router\Request;

class ApiKeyCheck implements MiddlewareInterface
{
    public function execute( array $parameters = [] ): void
    {
        $this->checkApiKey( $this->getApiKey(), $parameters['request'] );
    }

    /** @var array|string[][] Default rule to validate API key */
    private static array $rules = [
        'api_key' => [ 'required', 'type' => 'string', 'content' => '@^[a-zA-Z0-9]{5,64}$@', ],
    ];

    /** @var string Default API key */
    private string $api_key;

    /**
     * Returns API key
     *
     * @return string
     */
    private function getApiKey(): string
    {
        return '';
    }

    /**
     * Validates an API key by rule
     *
     * Rules could be redefined in the target class
     * Api key could be redefined in the target class
     *
     * @param $api_key
     *
     * @return void
     *
     * @throws ApiKeyExtensionException
     */
    protected function checkApiKey( $api_key, Request $request ): void
    {
        // No API key
        if( ! $this->getApiKey() ){
            return;
        }

        ValidationHelper::validate(
            $api_key
                ? [ 'api_key' => $api_key ]
                : $request->parameters,
            array_map(
                static fn( $rule ) => new Rule( $rule ),
                static::$rules
            )
        );

        $this->getApiKey() !== $request->parameters['api_key'] &&
            throw new ApiKeyExtensionException( 'Invalid API key', 403 );
    }
}