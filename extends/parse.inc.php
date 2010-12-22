<?php
Class parse
{
    /*
    let the magic ! less takes care of everything
    */
    public static function less_to_css()
    {
        LessCacheer::$extends->lessc->importDir  = LessCacheer::$conf['less_options']['importDir']; // define import Directories
        LessCacheer::$extends->lessc->debug_info = LessCacheer::$conf['less_options']['use_fireless']; // use fireless or not
        
        LessCacheer::$output = LessCacheer::$extends->lessc->parse(LessCacheer::$input); // parse the less file
        
        //if (LessCacheer::$conf['use_compression']) {
            $CSSC                = new CSSCompression(LessCacheer::$output, LessCacheer::$conf['compression_options']);
            LessCacheer::$output = $CSSC->css;
        //}
        
        if (LessCacheer::$conf['debug_info']) {
            LessCacheer::$extends->helpers->log("   Parsed files :\n");
            foreach (LessCacheer::$extends->lessc->allParsedFiles() as $key => $f) {
                LessCacheer::$extends->helpers->log("   * {$key}");
                if ($mixin = in_array(str_replace('\\', '/', $key), LessCacheer::$less_files['mixins'])) {
                    LessCacheer::$extends->helpers->log("     type : auto-imported mixin");
                }
                else if ($f['parent'] != null) {
                    LessCacheer::$extends->helpers->log("     type : user-imported less file");
                    LessCacheer::$extends->helpers->log("     imported by : {$f['parent']}");
                } //$f['parent'] != null
                else {
                    LessCacheer::$extends->helpers->log("     type : main less file");
                }
                LessCacheer::$extends->helpers->log("     last modification : " . date(DATE_RFC822, $f['filemtime']));
                LessCacheer::$extends->helpers->log("     next recache : " . date(DATE_RFC822, $f['filemtime'] + LessCacheer::$conf['cachetime']) . "\n");
            }
            LessCacheer::$debug_info .= "-------------------------------------------------------------- */\n";
            LessCacheer::$output = LessCacheer::$debug_info . LessCacheer::$output;
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