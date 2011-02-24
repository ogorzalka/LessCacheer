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
        // foreach each found config file
        foreach(self::get_config_files() as $config_file) {
            $conf = LessCacheer::$extends->yaml->load($config_file); // parse the yaml
            foreach((array)$conf as $key=>$value) {
                // if the key value is a reserved space : all, prod or dev
                if (in_array($key, array('all', 'prod', 'dev'))) {
                    $ignore_mode = LessCacheer::$conf['in_production'] === false ? 'prod' : 'dev';
                    if ($key == 'all' || $key == $ignore_mode) { continue; }
                    self::$less_config = array_merge_recursive($conf[$key], self::$less_config);
                } else {
                    // else, the config is determined with the get param !
                    if(!empty($_GET[$key])) {
                        foreach($conf[$key] as $getkey=>$getvalue) {
                            if ($_GET[$key] == $getkey) {
                                self::$less_config = array_merge_recursive($conf[$key][$getkey], self::$less_config);
                            }
                        }
                    }
                }
            }
        }
        // if there's a config for every mode, merge options with default conf if exists
        if (!empty($conf['all'])) {
            self::$less_config = array_merge_recursive($conf['all'], self::$less_config);
        }
    }
    
    public static function format_mixin_properties($props = array()) {
        $properties = array();
        foreach((array)$props as $key=>$value) {
            $properties[] = $value;
        }
        return "(".implode(';', $properties).")";
    }
    
    public static function add_less_variables() {
        foreach(self::$less_config as $var=>$val) {
            if ((bool)$val !== false) {
                if ($var == 'mixins') {
                    foreach($val as $key_mixin=>$value_mixin) {
                        if (!is_array($value_mixin) && !is_bool($value_mixin)) {
                            LessCacheer::$input .= "@$value_mixin"; 
                        } else {
                            LessCacheer::$input .= "@$key_mixin";
                            if (is_array($value_mixin)) {
                                LessCacheer::$input .= self::format_mixin_properties($value_mixin);
                            }
                        }
                        LessCacheer::$input .= "; ";
                    }
                } else {
                    LessCacheer::$input .= "@$var";
                    if (!is_bool($val)) {
                        LessCacheer::$input .= ":$val";
                    }
                    LessCacheer::$input .= ';';
                }
            }
        }
    }
    
    public static function init() {
        self::get_config_variables(); // retrieve the config variables
        self::add_less_variables(); // insert the variables inside the less input
    }
    
    function __construct() {
        
    }
}