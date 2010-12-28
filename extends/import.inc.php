<?php
Class import
{
    /*
    Find need less files
    */
    public static function collect_lessfiles()
    {
        LessCacheer::$less_files['mixins'] = LessCacheer::rglob(LessCacheer::$conf['mixins_path'] . '/*.less');
        LessCacheer::$less_files['user']   = LessCacheer::$f;
        
        // explode less files
        foreach (LessCacheer::$less_files as $key => $less_files) {
            foreach ((array) $less_files as $f) {
                if (file_exists($f)) {
                    LessCacheer::$input .= file::get($key, $f);
                }
            }
        }
    }
    
    public static function import_process()
    {
        if (LessCacheer::$recache === true) {
            self::collect_lessfiles();
        }
    }
    
    function __construct()
    {
    }
}