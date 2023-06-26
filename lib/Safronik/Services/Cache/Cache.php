<?php

namespace Safronik\Services\Cache;

use Safronik\Services\Serviceable;
use Safronik\Core\CodeTemplates\Service;

class Cache implements Serviceable
{
    use Service;
    
    private static array $request_types_to_cache = ['GET', 'HEAD'];
    private       string $cache_directory = __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    private       string $cache_file;
    
    public function __construct( string $cache_id, ?string $directory_with_cache = null )
    {
        $this->cache_directory = $directory_with_cache ?? $this->cache_directory;
        $this->cache_file      = $this->cache_directory . $cache_id;
        
        if( $this->isCached() ){
            die( $this->getCached() );
        }

        $this->startCaching();
    }
    
    public static function isRequestMethodShouldBeCached( $request_type )
    {
        return in_array( $request_type, self::$request_types_to_cache, true );
    }
    
    public function isCached(): bool
    {
        return file_exists( $this->cache_file );
    }
    
    public function getCached(): string
    {
        return file_get_contents( $this->cache_file );
    }
    
    public function startCaching(): void
    {
        ob_start( 'self::endCaching', 0, PHP_OUTPUT_HANDLER_STDFLAGS ^ PHP_OUTPUT_HANDLER_REMOVABLE);
    }
    
    public function endCaching( $data ): string
    {
        file_put_contents( $this->cache_file, 'cache!<br>' . $data );
        
        return $data;
    }
    
    public function __destruct()
    {
        $this->endCaching( ob_get_contents() );
    }
}