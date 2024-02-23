<?php

namespace Safronik\Services\Cache;

use Safronik\Core\CodeTemplates\Hydrator;
use Safronik\Core\CodeTemplates\Interfaces\Serviceable;
use Safronik\Core\CodeTemplates\Service;
use Safronik\Services\Request\Request;

class Cache implements Serviceable
{
    use Service;
    use Hydrator;
    
    // Options
    private array  $request_types_to_cache = ['GET', 'HEAD'];
    private string $cache_directory = __DIR__ . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    private array  $allowed_ttl = [
        'GET'  => '86400',
        'HEAD' => '86400',
    ];
    
    // Cache metadata
    private string $cache_file;
    
    // Metadata from file
    // @todo cache metadata file
    
    public function __construct( CacheOptions $options )
    {
        $this->setOptions( $options );
    }
    
    private function setOptions( CacheOptions $options )
    {
        $this->cache_directory        = $options->cache_directory        ?? $this->cache_directory;
        $this->request_types_to_cache = $options->request_types_to_cache ?? $this->request_types_to_cache;
        $this->allowed_ttl            = $options->allowed_ttl            ?? $this->allowed_ttl;
    }
    
    public function isMethodShouldBeCached( $request_type ): bool
    {
        return in_array( $request_type, $this->request_types_to_cache, true );
    }
    
    public function cache( Request $request ): void
    {
        $this->cache_file = $this->cache_directory . $request->path_id;
        
        if( $this->isCached() && ! $this->isModified() ){
            die( $this->getCached() );
        }
        
        $this->startCaching();
    }
    
    public function isCached(): bool
    {
        return file_exists( $this->cache_file );
    }
    
    private function isModified()
    {
        return false;
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
        file_put_contents(
            $this->cache_file,
            'cache!<br>' . $data
        );
        
        //@todo append metadata
        
        return $data;
    }
    
    public function __destruct()
    {
        $this->endCaching( ob_get_contents() );
    }
}