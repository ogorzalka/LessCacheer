<?php
Class yml
{
    public static $yaml;
    
    public static function load($file)
    {
        return self::$yaml->load($file);
    }
    
    function __construct()
    {
        require('yaml/Yaml.php');
        self::$yaml = new Yaml();
    }
}