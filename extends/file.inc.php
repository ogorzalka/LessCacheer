<?php
Class file extends LessCacheer
{
    /**
     * Returns the last modified date of a cache file
     *
     * @param $file
     * @return int
     */
    public static function modified($file)
    {
        return (file_exists($file)) ? (int) filemtime($file) : 0;
    }
    
    public static function get($type = 'user', $input, $force_recache = false)
    {
        $basename = basename($input); // filename
        
        if ($type == 'mixins') {
            if (!in_array(dirname($input) . '/', LessCacheer::$conf['less_options']['importDir'])) {
                LessCacheer::$conf['less_options']['importDir'][] = dirname($input) . '/';
            }
            $data = "@import '$basename';";
        } else {
            $data = file_get_contents($input);
        }
        return $data;
    }
    
    public static function need_to_recache()
    {
        return (!LessCacheer::$conf['in_production'] || (!file_exists(LessCacheer::$conf['cached_f']) || self::modified(LessCacheer::$f) > self::modified(LessCacheer::$conf['cached_f'])) && LessCacheer::$conf['in_production']);
    }
    
    public static function get_contents($path)
    {
        $output = file_get_contents($path);
        return $output;
    }
    
    public static function init()
    {
        // just add the folder of the current parsed css as import directory
        
        if (self::need_to_recache()) {
            LessCacheer::$recache                             = true;
            LessCacheer::$conf['less_options']['importDir'][] = dirname(LessCacheer::$f) . '/';
            LessCacheer::$extends->helpers->log("   Just recached !\n");
        }
    }
    
    function __construct()
    {
    }
}