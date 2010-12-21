<?php
Class helpers
{
    public static function clean_path($p)
    {
        $p = str_replace(array(
            '\\',
            '//'
        ), '/', $p);
        return $p;
    }
    
    /*
    add debug infos
    */
    function log($str)
    {
        if (empty(LessCacheer::$debug_info)) {
            LessCacheer::$debug_info = "/* --------------------------------------------------------------\n\n";
            LessCacheer::$debug_info .= "                            Debug Infos\n\n";
        }
        LessCacheer::$debug_info .= $str . "\n";
    }
    
    function __construct()
    {
    }
}