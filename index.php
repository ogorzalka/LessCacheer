<?php
Class LessCacheer
{
    static $file_cache;
    /**
     * Any files that are found with find_file are stored here so that
     * any further requestes for the files are just given the path
     * from this array, rather than searching for the file again.
     *
     * @var array
     */
    public static $f = ''; // requested less files to parse
    public static $css = '';
    public static $mixin_file = '';
    public static $lessfiles = '';
    public static $parsed_css = '';
    public static $mixin_files = array(); // loaded mixins
    public static $less_files = array(); // loaded css files
    public static $recache;
    public static $compression_id = '';
    public static $cached_filename = '';
    public $headers;
    public $conf = array(
        'install_path' => '', 
        'mixins_path' => 'lessphp/mixins', 
        'cache_mixins' => true, 
        'in_production' => true, 
        'cachetime' => 1314000, 
        'use_compression' => false, 
        'less_options' => array(
            'importDir' => array()
        ),
        'compression_options' => array(
            // Converts long color names to short hex names
            // (aliceblue -> #f0f8ff)
            'color-long2hex' => true, 
            // Converts rgb colors to hex
            // (rgb(159,80,98) -> #9F5062, rgb(100%) -> #FFFFFF)
            'color-rgb2hex' => true, 
            // Converts long hex codes to short color names (#f5f5dc -> beige)
            // Only works on latest browsers, careful when using
            'color-hex2shortcolor' => false, 
            // Converts long hex codes to short hex codes
            // (#44ff11 -> #4f1)
            'color-hex2shorthex' => true, 
            // Converts font-weight names to numbers
            // (bold -> 700)
            'fontweight2num' => true, 
            // Removes zero decimals and 0 units
            // (15.0px -> 15px || 0px -> 0)
            'format-units' => true, 
            // Lowercases html tags from list
            // (BODY -> body)
            'lowercase-selectors' => true, 
            // Add space after pseduo selectors, for ie6
            // (a:first-child{ -> a:first-child {)
            'pseudo-space' => false, 
            // Compresses single defined multi-directional properties
            // (margin: 15px 25px 15px 25px -> margin:15px 25px)
            'directional-compress' => true, 
            // Combines multiply defined selectors
            // (p{color:blue;} p{font-size:12pt} -> p{color:blue;font-size:12pt;})
            'multiple-selectors' => true, 
            // Combines selectors with same details
            // (p{color:blue;} a{color:blue;} -> p,a{color:blue;})
            'multiple-details' => true, 
            // Combines color/style/width properties
            // (border-style:dashed;border-color:black;border-width:4px; -> border:4px dashed black)
            'csw-combine' => true, 
            // Combines cue/pause properties
            // (cue-before: url(before.au); cue-after: url(after.au) -> cue:url(before.au) url(after.au))
            'auralcp-combine' => true, 
            // Combines margin/padding directionals
            // (margin-top:10px;margin-right:5px;margin-bottom:4px;margin-left:1px; -> margin:10px 5px 4px 1px;)
            'mp-combine' => true, 
            // Combines border directionals
            // (border-top|right|bottom|left:1px solid black -> border:1px solid black)
            'border-combine' => true, 
            // Combines font properties
            // (font-size:12pt; font-family: arial; -> font:12pt arial)
            'font-combine' => true, 
            // Combines background properties
            // (background-color: black; background-image: url(bgimg.jpeg); -> background:black url(bgimg.jpeg))
            'background-combine' => true, 
            // Combines list-style properties
            // (list-style-type: round; list-style-position: outside -> list-style:round outside)
            'list-combine' => true, 
            // Removes the last semicolon of a property set
            // ({margin: 2px; color: blue;} -> {margin: 2px; color: blue})
            'unnecessary-semicolons' => true, 
            // Readibility of Compressed Output, Defaults to none
            'readability' => 3
        )
    );
    
    
    /**
     * Include paths
     *
     * These are used for finding files on the system. Rather than
     * using PHP's built-in include paths, we just store the paths
     * in this array and use the find_file function to locate it.
     *
     * @var array
     */
    
    static function rglob($pattern, $flags = 0, $path = '')
    {
        if (!$path && ($dir = dirname($pattern)) != '.') {
            if ($dir == '\\' || $dir == '/')
                $dir = '';
            return self::rglob(basename($pattern), $flags, $dir . '/');
        }
        $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
        $files = glob($path . $pattern, $flags);
        if (is_array($paths) && is_array($files)) {
            foreach ($paths as $p)
                $files = array_merge($files, self::rglob($pattern, $flags, $p . '/'));
        }
        return is_array($files) ? $files : array();
    }
    
    /**
     * Renders the CSS
     *
     * @param $output What to display
     * @return void
     */
    function render_css($output, $level = false)
    {
        $length = strlen($output);
        $modified = ($this->conf['in_production']) ? file::modified($this->cached_filename) : file::modified($this->less_files['user'][0]);
        $lifetime = ($this->conf['in_production'] === true) ? $this->conf['cachetime'] : 0;

        headers::generate($modified, $lifetime, $length);
        // gzip, zlib handler
        $output = headers::set_compression($output, $level);
        
        # Send the headers
        headers::send();
        
        echo $output;
        exit;
    }
    
    function insert_explode($sep, $str, $addition, $mode = 'before')
    {
        $explode = explode($sep, $str);
        foreach ($explode as $e) {
            $array_output[] = ($mode == 'before') ? $addition . $e : $e . $addition;
        }
        return $array_output;
    }
    
    function collect_lessfiles()
    {
        $lessfiles = '';
        // mixins import
        if (!$this->mixin_file = DataCache::Get("mixins", "mixin_file")) {
            $this->less_files['mixins'] = self::rglob($this->conf['base_path'] . $this->conf['mixins_path'] . '/*.less');
        }
        
        $this->less_files['user'] = $this->insert_explode(',', $this->f, $this->conf['origin_install']);
        // explode less files
        foreach ($this->less_files as $key => $less_files) {
            foreach ($less_files as $f) {
                if (file_exists($f)) {
                    if ($key == 'user') {
                        if (!in_array(dirname($f) . '/', $this->conf['less_options']['importDir'])) {
                            $this->conf['less_options']['importDir'][] = dirname($f) . '/';
                        }
                    }
                    $lessfiles .= file::cache($f, $key);
                }
            }
        }
        return $lessfiles;
    }
    
    function generate_paths()
    {
        $sapi = 'undefined';
        if (!strstr($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_NAME']) && ($sapi = @php_sapi_name()) == 'cgi') {
            $script_name = $_SERVER['PHP_SELF'];
        } else {
            $script_name = $_SERVER['SCRIPT_NAME'];
        }
        $a = explode("/" . $this->conf['install_path'], str_replace("\\", "/", dirname($script_name)));
        if (count($a) > 1)
            array_pop($a);
        $url = implode($this->conf['install_path'], $a);
        reset($a);
        $a = explode($this->conf['install_path'], str_replace("\\", "/", dirname(__FILE__)));
        if (count($a) > 1)
            array_pop($a);
        $pth = implode($this->conf['install_path'], $a);
        unset($a);
        $this->conf['base_url']       = $url . (substr($url, -1) != "/" ? "/" : "");
        $this->conf['base_path']      = $pth . (substr($pth, -1) != "/" && substr($pth, -1) != "\\" ? "/" : "");
        //$this->conf['origin_path'] = 
        $this->conf['folder_install'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->conf['base_path']);
        
        $this->conf['origin_install'] = (in_array($this->conf['folder_install'], array(
            '/',
            ''
        ))) ? $this->conf['base_path'] : str_replace($this->conf['folder_install'], '', $this->conf['base_path']);
        ;
        
        // assign site_url
        $this->conf['site_url'] = 'http://';
        $this->conf['site_url'] .= $_SERVER['HTTP_HOST'];
        $this->conf['site_url'] = str_replace(':' . $_SERVER['SERVER_PORT'], '', $this->conf['site_url']); // remove port from HTTP_HOST  
        $this->conf['site_url'] .= $this->conf['base_url'];
    }
    
    function merge_options($user_conf)
    {
        $arrays = func_get_args();
        $base   = array_shift($arrays);
        if (!is_array($base))
            $base = empty($base) ? array() : array(
                $base
            );
        foreach ($arrays as $append) {
            if (!is_array($append))
                $append = array(
                    $append
                );
            foreach ($append as $key => $value) {
                if (!array_key_exists($key, $base) and !is_numeric($key)) {
                    $base[$key] = $append[$key];
                    continue;
                }
                if (is_array($value) or is_array($base[$key])) {
                    $base[$key] = $this->merge_options($base[$key], $append[$key]);
                } else if (is_numeric($key)) {
                    if (!in_array($value, $base))
                        $base[] = $value;
                } else {
                    $base[$key] = $value;
                }
            }
        }
        return $base;
    }
    
    function less_to_css($input) {
        $less = new lessc(); // instantiate Less

        // retrieve the main merged less file alias
        $cache_name = file::make_alias($this->f);
        
        $this->cached_filename = DataCache::getFilename($cache_name, 'mainless'); // retrieve the cached filename
        
        // if we need to cache again, we unlink the previous cached file
        if ($this->recache == true) {
            @unlink($this->cached_filename);
        }

        // if there's no cache file
        if (!$this->conf['in_production'] || !$parsed_css = DataCache::Get($cache_name, 'mainless')) {
            $parsed_css = $less->parse($input, $this->conf['less_options']); // parse the less file
            if ($this->conf['use_compression']) {
                $CSSC             = new CSSCompression($parsed_css, $this->conf['compression_options']);
                $parsed_css = $CSSC->css;
            }
            if ($this->conf['in_production']) {
                DataCache::Put($cache_name, 'mainless', $this->conf['cachetime'], $parsed_css); // put data inside the cache
            }
        }
        return $parsed_css;
    }
    
    function __construct($f)
    {
        require('config.inc.php');
        require 'lessphp/lessc.inc.php';
        require 'helpers/css-compressor/src/CSSCompression.inc';
        
        // auto include extends
        $extends = self::rglob('extends/*.class.php');
        foreach ($extends as $extend) {
            require $extend;
            $classname = str_replace('.class.php', '', basename($extend));
        }
        
        $this->f = $f; // this less files !

        try {
            $this->recache = false; // init of recache
            $this->conf    = $this->merge_options($this->conf, $conf); // make conf usable by all methods
            // if production mode -> use cache
            // if compression is ON
            $this->compression_id = ($this->conf['use_compression']) ? md5(serialize($this->conf['compression_options'])) : 'nocompress';
            $this->generate_paths(); // generate path config
            $this->lessfiles = $this->collect_lessfiles(); // include every less you take !
            
            /**
             * Parse the collected Less Files
             */
            $this->css = $this->less_to_css($this->lessfiles);
            $this->render_css($this->css); // print the final css
        }
        /**
         * If any errors were encountered
         */
        catch (Exception $e) {
            headers::set('_status', 500);
            /** 
             * The message returned by the error 
             */
            $message = $e->getMessage();
            $trace   = $e->getTrace();
            $title   = $trace[0]['function'];
            $file    = $f;
            /** 
             * Load in the error view
             */
            headers::send();
            require 'view/less_error.php';
        }
    }
    
}
$less = new LessCacheer($_GET['f']);