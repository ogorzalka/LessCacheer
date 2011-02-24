<?php
class helpers
{
    public static function clean_path($p)
    {
        $p = str_replace(array(
            '\\',
            '//'
        ), '/', $p);
        return $p;
    }

    
    function __construct()
    {
    }
}