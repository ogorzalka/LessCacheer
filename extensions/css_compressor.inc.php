<?php
class css_compressor
{
    public static function init() {
        LessCacheer::$conf['use_compression'] = (LessCacheer::$conf['in_production']) === true ? LessCacheer::$conf['use_compression'] : false;
    }
    
    public static function preconfig () {
        LessCacheer::$conf['cached_f_suffix'] = md5(serialize(LessCacheer::$conf['compression_options']));
    }
    
    public static function after_parse_process() {
        if (LessCacheer::$conf['use_compression']) {
            $CSSC                = new CSSCompression(LessCacheer::$output, LessCacheer::$conf['compression_options']);
            LessCacheer::$output = $CSSC->css;
        }
    }
    
    function __construct()
    {
        require('css-compressor/src/CSSCompression.inc');
    }
}