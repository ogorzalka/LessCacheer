<?php
class lessfile_config {
    public static $less_config = array();
    
    public static function get_config_files() {
        $found = array();
        $dir = dirname(LessCacheer::$f).'/';
        
        // config files to search
        $config_files = array(
            $dir.'config.yml',
            $dir.str_replace(array('.css', '.less'), '.yml', basename(LessCacheer::$f)),
        );
        
        // foreach each existing config file
        foreach($config_files as $config_file) {
            if (file_exists($config_file)) {
                $found[] = $config_file;
            }
        }
        return $found;
    }
    
    public static function get_config_variables() {
        // foreach each existing config file
        foreach(self::get_config_files() as $config_file) {
            $conf = LessCacheer::$extends->yml->load($config_file); // parse the yaml
            foreach((array)$conf as $key=>$value) {
                // if the key value is a reserved space : all, prod or dev
                if (in_array($key, array('all', 'prod', 'dev'))) {
                    $ignore_mode = LessCacheer::$conf['in_production'] === false ? 'prod' : 'dev';
                    if ($key == 'all' || $key == $ignore_mode) { continue; }
                    self::$less_config = array_merge($conf[$key], self::$less_config);
                } else {
                    // else, the config is determined with the get param !
                    if(!empty($_GET[$key])) {
                        foreach($conf[$key] as $getkey=>$getvalue) {
                            if ($_GET[$key] == $getkey) {
                                self::$less_config = array_merge($conf[$key][$getkey], self::$less_config);
                            }
                        }
                    }
                }
            }
        }
        
        // if there's a config for every mode, merge options with default conf if exists
        if (!empty($conf['all'])) {
            self::$less_config = array_merge($conf['all'], self::$less_config);
        }
    }

    public static function add_less_variables() {
        foreach(self::$less_config as $var=>$val) {
            LessCacheer::$input .= "@$var:$val;";
        }
    }
    
    public static function init() {
        self::get_config_variables(); // retrieve the config variables
        self::add_less_variables(); // insert the variables inside the less input
    }
    
    function __construct() {
        
    }
}