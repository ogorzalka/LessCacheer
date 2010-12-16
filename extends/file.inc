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
        $basename        = basename($input); // filename

        if ($type == 'mixins') {
            if (!in_array(dirname($input).'/', LessCacheer::$conf['less_options']['importDir'])) {
                LessCacheer::$conf['less_options']['importDir'][] = dirname($input).'/';
            }
            $data                                      = "@import '$basename';";
        } else {
            $data = file_get_contents($input);
        }
        return $data;
    }
    
    public static function need_to_recache() {
        return (
            !LessCacheer::$conf['in_production'] ||
            (!file_exists(LessCacheer::$cached_f) || file::modified(LessCacheer::$f) > file::modified(LessCacheer::$cached_f)) && LessCacheer::$conf['in_production']
        );
    }
    
    public static function get_contents($path) {
        LessCacheer::$output = file_get_contents($path);
        return $this;
    }
}