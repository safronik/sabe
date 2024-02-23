<?php

namespace Safronik\Services\Cache;

class CacheOptions{
    public array  $request_types_to_cache;
    public string $cache_directory;
    public array  $allowed_ttl;
}