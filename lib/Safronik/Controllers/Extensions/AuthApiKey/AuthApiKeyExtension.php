<?php

namespace Safronik\Controllers\Extensions\AuthApiKey;

use Safronik\Controllers\Extensions\AuthApiKey\Exceptions\AuthApiKeyExtensionException;
use Safronik\Helpers\ValidationHelper;

trait AuthApiKeyExtension{
    
    /** @var array|string[][] Default rule to validate API key */
    protected static array $rules = [
        'api_key' => [ 'required', 'type' => 'string', 'content' => '@^[a-zA-Z0-9]{5,64}$@', ],
    ];
    
    /** @var string Default API key */
    protected string $api_key;
    
    /**
     * Returns API key
     *
     * @return string
     */
    abstract public function getApiKey(): string;
    
    /**
     * Validates API key by rule
     *
     * Rules could be redefined in the target class
     * Api key could be redefined in the target class
     *
     * @param $api_key
     *
     * @return void
     * @throws AuthApiKeyExtensionException
     */
    protected function checkApiKey( $api_key = null ): void
    {
        // Skip
        if( $this->getApiKey() === '' ){
            return;
        }
        
        ValidationHelper::validate(
            $api_key
                ? [ 'api_key' => $api_key ]
                : $this->request->parameters,
            static::$rules
        );
        
        $this->getApiKey() !== $this->request->parameters['api_key'] &&
            throw new AuthApiKeyExtensionException( 'Invalid API key', 403 );
    }
}