<?php
Class css_compressor
{
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