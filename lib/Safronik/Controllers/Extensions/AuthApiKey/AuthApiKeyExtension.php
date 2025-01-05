<?php

namespace Safronik\Controllers\Extensions\AuthApiKey;

use Safronik\Controllers\Extensions\AuthApiKey\Exceptions\AuthApiKeyExtensionException;
use Safronik\Core\ValidationHelper;
use Safronik\Models\Entities\Rule;

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
    abstract protected function getApiKey(): string;
    
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
        // No API key
        if( ! $this->getApiKey() ){
            return;
        }
        
        ValidationHelper::validate(
            $api_key
                ? [ 'api_key' => $api_key ]
                : $this->request->parameters,
            array_map( static fn( $rule ) => new Rule( $rule ), static::$rules )
        );
        
        $this->getApiKey() !== $this->request->parameters['api_key'] &&
            throw new AuthApiKeyExtensionException( 'Invalid API key', 403 );
    }
}