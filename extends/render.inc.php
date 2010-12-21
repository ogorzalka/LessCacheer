<?php
class render {
    
    /**
     * Return the CSS
     *
     * @param $output What to display
     * @return void
     */
    public static function render_css($level = false) {
        $length   = strlen(LessCacheer::$output);
        $modified = (LessCacheer::$conf['in_production'] === true) ? LessCacheer::$extends->file->modified(LessCacheer::$conf['cached_f']) : file::modified(LessCacheer::$f);
        $lifetime = (LessCacheer::$conf['in_production'] === true) ? LessCacheer::$conf['cachetime'] : 0;
        
        LessCacheer::$extends->headers->generate($modified, $lifetime, $length);
        // gzip, zlib handler
        LessCacheer::$output = headers::set_compression(LessCacheer::$output, $level);
        
        # Send the headers
        headers::send();
        echo LessCacheer::$output;
        exit;
    }
    
    public static function rendering_process() {
        self::render_css();
    }
    
    function __construct() {}
}