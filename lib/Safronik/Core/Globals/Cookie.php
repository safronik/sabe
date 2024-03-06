<?php

namespace Safronik\Core\Globals;

use Safronik\Core\CodeTemplates\Singleton;

final class Cookie extends Variables{
    
	protected function getVariable( $name )
	{
		if ( function_exists('filter_input') ) {
            $value = filter_input(INPUT_COOKIE, $name);
        }

        if ( empty($value) ) {
            $value = $_COOKIE[ $name ] ?? '';
        }

        return $value;
	}
	
    protected function getAllVariablesNames(): array
    {
        return array_keys( $_COOKIE );
    }
    
    public static function set($name, $value = '', $expires = 0, $path = '', $domain = '', $secure = null, $httponly = false, $samesite = 'Lax')
    {
	    $secure = ! is_null( $secure )
		    ? $secure
		    : in_array( Server::get( 'HTTPS' ), ['on', '1', true]) || Server::get( 'SERVER_PORT' ) == 443;

        // For PHP 7.3+ and above
	    if ( version_compare( phpversion(), '7.3.0', '>=' ) ) {
            $params = array(
                'expires'  => $expires,
                'path'     => $path,
                'domain'   => $domain,
                'secure'   => $secure,
                'httponly' => $httponly,
            );

            if ( $samesite ) {
                $params['samesite'] = $samesite;
            }

            /** @psalm-suppress InvalidArgument */
            $out = setcookie($name, $value, $params);

            // For PHP 5.6 - 7.2
        } else {
            $out = setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
        }

        return $out;
    }
}