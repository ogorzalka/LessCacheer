<?php
class hooks {
    /**
     * Allows modules to hook into the processing at any point
     *
     * @param $method The method to check for in each of the modules
     * @return boolean
     */
     
     
    public static function add($method, $params = array())
    {
        foreach (LessCacheer::$modules as $module_name => $module) {
            if (method_exists($module, $method)) {
                call_user_func_array(array($module_name,$method),$params);
            }
        }
    }
    
    function __construct() {}
    
}