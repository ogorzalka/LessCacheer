<?php
class cacheer
{
    /**
     * Renders the CSS
     *
     * @param $output What to display
     * @return void
     */
    public static function cache()
    {
        if (LessCacheer::$conf['in_production'] === true && LessCacheer::$recache === true) {
            $path = '';
            foreach (explode('/', LessCacheer::$conf['filecache_path']) as $folder) {
                if ($folder != '' && !file_exists(LessCacheer::$conf['base_path'] . $path . $folder)) {
                    mkdir(LessCacheer::$conf['base_path'] . $path . $folder, 0777);
                }
                $path .= $folder . '/';
            }
            file_put_contents(LessCacheer::$conf['cached_f'], LessCacheer::$output);
        } else if (LessCacheer::$conf['in_production'] === true && LessCacheer::$recache === false) {
            LessCacheer::$output = file_get_contents(LessCacheer::$conf['cached_f']);
        } else {
            if (file_exists(LessCacheer::$conf['cached_f'])) {
                unlink(LessCacheer::$conf['cached_f']);
            }
        }
    }
    public static function caching_process()
    {
        self::cache();
    }
    
    function __construct()
    {
    }
}