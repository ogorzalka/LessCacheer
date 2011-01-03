<?php
class vendor_properties {
	/**
	 * Defines the build-in vendor properties
	 * @var array built-in vendor properties
	 * @see vendor_properties
	 */
	private static $vendor_properties = array(
        // require vendor-prefixes mixins : css3/vendor-prefixes.less
		'border-radius',
		'border-top-right-radius',
		'border-bottom-right-radius',
		'border-bottom-left-radius',
		'border-top-left-radius',
		'box-shadow',
		'box-sizing',
		'opacity',
		'transform',
		'transform-origin',
		'transition',
    	'transition-property',
    	'transition-duration',
    	'transition-timing-function',
    	'transition-delay',
    	'background-size',
    	
    	// require multi-column mixins : css3/multi-column.less
    	'column-width',
    	'column-count',
    	'column-rule',
    	'column-rule-color',
    	'column-rule-style',
    	'column-rule-width',
    	'column-gap',
        'column-span',
	);
	
	public static function preparse_process() {
	    if (LessCacheer::$conf['vendor_properties'] === true) {
    	    foreach(self::$vendor_properties as $property) {
    	        if (preg_match_all('#^\s*?\t*?'.$property.'\s*:\s*([^;\n]*)(;)?#m', LessCacheer::$input, $out)) {
    	            list($property_lines, $property_value, $separator) = $out;
    	            $new_property_lines = array();
    	            foreach($property_lines as $key=>$property_line) {
    	                $new_property_lines[$key] = "@".$property.'('.trim($property_value[$key]).');';
    	            }
    	            LessCacheer::$input = str_replace($property_lines, $new_property_lines, LessCacheer::$input);
    	        }
    	    }
        }
	}
	
    function __construct() {
        
    }
}