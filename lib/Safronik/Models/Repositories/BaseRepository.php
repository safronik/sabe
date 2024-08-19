<?php

namespace Safronik\Models\Repositories;

use Safronik\DB\DB;

abstract class BaseRepository{
    
    protected DB $db;
    
    public function __construct( DB $db )
    {
        $this->db = $db;
    }
}