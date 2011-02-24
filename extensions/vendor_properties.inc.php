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
	        if (preg_match_all('#^\s*?\t*?('.implode('|', self::$vendor_properties).')\s*:\s*([^;\n]*)(;)?#m', LessCacheer::$input, $out)) {
	            list($property_lines, $property, $property_value) = $out;
	            $updated_property_lines = array();
	            foreach($property_lines as $key=>$property_line) {
	                $updated_property_lines[$key] = "@".$property[$key].'('.trim($property_value[$key]).');';
	            }
	            LessCacheer::$input = str_replace($property_lines, $updated_property_lines, LessCacheer::$input);
	        }
        }
	}
	
    function __construct() {
        
    }
}