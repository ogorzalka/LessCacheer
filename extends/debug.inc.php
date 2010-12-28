<?php
class debug {

    /*
    add debug infos
    */
    public static function log($str)
    {
        if (empty(LessCacheer::$debug_info)) {
            LessCacheer::$debug_info = "/* --------------------------------------------------------------\n\n";
            LessCacheer::$debug_info .= "                            Debug Infos\n\n";
        }
        LessCacheer::$debug_info .= $str . "\n";
    }
    
    public static function after_parse_process() {
        if (LessCacheer::$conf['debug_info']) {
            self::log("   Parsed files :\n");
            foreach (LessCacheer::$extends->lessc->allParsedFiles() as $key => $f) {
                self::log("   * {$key}");
                if ($mixin = in_array(str_replace('\\', '/', $key), LessCacheer::$less_files['mixins'])) {
                    self::log("     type : auto-imported mixin");
                }
                else if ($f['parent'] != null) {
                    self::log("     type : user-imported less file");
                    self::log("     imported by : {$f['parent']}");
                } //$f['parent'] != null
                else {
                    self::log("     type : main less file");
                }
                self::log("     last modification : " . date(DATE_RFC822, $f['filemtime']));
                self::log("     next recache : " . date(DATE_RFC822, $f['filemtime'] + LessCacheer::$conf['cachetime']) . "\n");
            }
            LessCacheer::$debug_info .= "-------------------------------------------------------------- */\n";
            LessCacheer::$output = LessCacheer::$debug_info . LessCacheer::$output;
        }
    }
    
    function __construct() {
        
    }
}