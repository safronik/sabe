<?php

if( ! function_exists('com_create_guid') ){
    
    /**
     * com_create_guid
     * Generates GUID
     *
     * @return string
     */
    function com_create_guid()
    {
        return sprintf(
            '{%04X%04X-%04X-%04X-%04X-%04X%04X%04X}',
            random_int( 0, 65535 ),
            random_int( 0, 65535 ),
            random_int( 0, 65535 ),
            random_int( 16384, 20479 ),
            random_int( 32768, 49151 ),
            random_int( 0, 65535 ),
            random_int( 0, 65535 ),
            random_int( 0, 65535 )
        );
    }
}
