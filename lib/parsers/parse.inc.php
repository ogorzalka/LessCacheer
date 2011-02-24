<?php
class parse
{
    /*
    let the magic ! less takes care of everything
    */
    public static function less_to_css()
    {
        if (LessCacheer::$to_parse === true) {
            LessCacheer::$extends->lessc->importDir  = LessCacheer::$conf['less_options']['importDir']; // define import Directories
            LessCacheer::$extends->lessc->debug_info = LessCacheer::$conf['less_options']['use_fireless']; // use fireless or not
            LessCacheer::$output = LessCacheer::$extends->lessc->parse(LessCacheer::$input); // parse the less file
        } else {
            LessCacheer::$output = LessCacheer::$input;
        }
    }
    
    public static function parse_process()
    {
        if (LessCacheer::$recache === true) {
            self::less_to_css();
        }
    }
    
    function __construct()
    {
    }
}