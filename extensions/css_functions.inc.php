<?php
class css_functions {

	/**
	 * @var array
	 */
	public static $functions = array();
	
	/**
	 * Register a new function
	 * @access public
	 * @param $name
	 * @param $map
	 * @return void
	 */
	public static function register($name,$map = array())
	{
		self::$functions[$name] = $map;
	}
	
	/**
	 * Finds CSS 'functions'. These are things like url(), embed() etc.
	 * Handles interior brackets as well by using recursion.
	 * Also handles nested functions.
	 * @access public
	 * @param $name
	 * @param $string
	 * @return array
	 */
	public static function find_functions($name,$string)
	{
		$return = array();
		$regex ="/{$name}(\s*\(\s*((?:(?0)|(?1)|[^()]+)*)\s*\)\s*)/sx";
		
		if(preg_match_all($regex, $string, $match))
		{
			foreach($match[0] as $key => $value)
			{
				$params = $match[2][$key];
				
				// Encode commas in between braces so we can have nested functions
				if(preg_match_all('/\(.*?\,.*?\)/',$match[2][$key],$nested_commas))
				{
					foreach($nested_commas[0] as $key => $commas)
					{
						$replace = str_replace(',','#COMMA#',$commas);
						$params = str_replace($commas,$replace,$params);
					}
				}
				
				// Break the params into an array
				$params = explode(',',$params);
				
				// Decode commas
				foreach($params as $param_key => $param)
				{
					$params[$param_key] = str_replace('#COMMA#',',',$param);
				}
				
				$return[] = array(
					'string' => $value,
					'params' => $params
				);
				
			}
		}
		
		return $return;
	}
	
	/**
	 * @access public
	 * @return string
	 */
	public static function preparse_process()
	{
		// Go through each custom function
		foreach(self::$functions as $name => $function)
		{
			$obj 	= $function[0];
			$method = $function[1];

			// Find them in the CSS
			foreach(self::find_functions($name,LessCacheer::$input) as $found)
			{				
				// Call the hook method for this function
				$result = call_user_func_array(array($obj,$method),$found['params']);
				
				// Replace it in the CSS
				LessCacheer::$input = str_replace($found['string'],$result,LessCacheer::$input);
			}
		}
	}

	/**
	 * Lets extensions register custom functions by creating a hook
	 * @access public
	 * @return void
	 */
	public static function init()
	{
		hooks::add('register_function');
	}
	
	function __construct() {
	}
	
}