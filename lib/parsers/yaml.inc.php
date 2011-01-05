<?php
class yaml
{
    public static $yaml;
    
    public static function load($file)
    {
        return self::$yaml->loadFile($file);
    }
    
    function __construct()
    {
        require('yaml/Spyc.php');
        self::$yaml = new Spyc();
    }
}