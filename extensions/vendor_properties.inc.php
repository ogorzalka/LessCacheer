<?php
class vendor_properties {
	/**
	 * Defines the build-in vendor properties
	 * @var array built-in vendor properties
	 * @see vendor_properties
	 */
	private static $vendor_properties = array(
		'border-radius' => array(
			'-moz-border-radius',
			'-webkit-border-radius',
			'-khtml-border-radius'
		),
		'border-top-right-radius' => array(
			'-moz-border-radius-topright',
			'-webkit-border-top-right-radius',
			'-khtml-border-top-right-radius'
		),
		'border-bottom-right-radius' => array(
			'-moz-border-radius-bottomright', 
			'-webkit-border-bottom-right-radius',
			'-khtml-border-bottom-right-radius'
		),
		'border-bottom-left-radius' => array(
			'-moz-border-radius-bottomleft',
			'-webkit-border-bottom-left-radius',
			'-khtml-border-bottom-left-radius'
		),
		'border-top-left-radius' => array(
			'-moz-border-radius-topleft',
			'-webkit-border-top-left-radius',
			'-khtml-border-top-left-radius'
		),
		'box-shadow' => array('-moz-box-shadow', '-webkit-box-shadow'),
		'box-sizing' => array('-moz-box-sizing', '-webkit-box-sizing'),
		'opacity' => array('-moz-opacity', '-webkit-opacity', '-khtml-opacity'),
		'transform' => array(
			'-moz-transform',
			'-webkit-transform',
			'-khtml-transform'
		),
		'transform-origin' => array(
			'-moz-transform-origin',
			'-webkit-transform-origin',
			'-khtml-transform-origin'
		),
		'transition' => array(
			'-moz-transition',
			'-webkit-transition',
			'-khtml-transition'
        ),
    	'transition-property' => array(
			'-moz-transition-property',
			'-webkit-transition-property',
			'-khtml-transition-property'
    	),
    	'transition-duration' => array(
			'-moz-transition-duration',
			'-webkit-transition-duration',
			'-khtml-transition-duration'
    	),
    	'transition-timing-function' => array(
			'-moz-transition-timing-function',
			'-webkit-transition-timing-function',
			'-khtml-transition-timing-function'
    	),
    	'transition-delay' => array(
			'-moz-transition-delay',
			'-webkit-transition-delay',
			'-khtml-transition-delay'
    	),
	);
	
	public static function preparse_process() {
	    if (LessCacheer::$conf['vendor_properties'] === true) {
    	    foreach(self::$vendor_properties as $property=>$vendor_properties) {
    	        if (preg_match_all('#^\s*'.$property.'\s*:\s*([^;\n]*)(;)?#m', LessCacheer::$input, $out)) {
    	            list($property_lines, $property_value, $separator) = $out;
    	            $new_property_lines = array();
    	            foreach($property_lines as $key=>$property_line) {
    	                $new_property_lines[$key] = $property.':'.trim($property_value[$key]).';';
    	                foreach ($vendor_properties as $v_property) {
    	                    $new_property_lines[$key] .= "\n".$v_property.':'.trim($property_value[$key]).';';
    	                }
    	            }
    	            LessCacheer::$input = str_replace($property_lines, $new_property_lines, LessCacheer::$input);
    	        }
    	    }
        }
	}
	
    function __construct() {
        
    }
}