<?php
Class LessCacheer
{
    /**
     * Any files that are found with find_file are stored here so that
     * any further requestes for the files are just given the path
     * from this array, rather than searching for the file again.
     *
     * @var array
     */
    public static $f = ''; // requested less files to parse
    public static $recache = false; // init of recache
    public static $cached_f = ''; // requested cached file
    public static $input = '';
    public static $output = '';
    public static $lessfiles = '';
    public static $parsed_css = '';
    public static $less_files = array(); // loaded less files
    public static $less; // less object
    public static $debug_info = null;
    public static $headers;
    public static $conf = array('mixins_path' => 'mixins', 'cache_path' => 'cache', 'debug_info' => true, // display original line and less files within Fireless addons for Firefox
        'in_production' => true, 'cachetime' => 1314000, 'use_compression' => false, 'less_options' => array('use_fireless' => false, 'importDir' => array()), 'compression_options' => array(
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
        'readability' => 3));


	/**
	 * Allows modules to hook into the processing at any point
	 *
	 * @param $method The method to check for in each of the modules
	 * @return boolean
	 */
	private static function hook($method)
	{
		foreach(self::$modules as $module_name => $module)
		{
			if(method_exists($module,$method))
			{				
				call_user_func(array($module_name,$method));
			}
		}
	}    
    
    /*
    Merge user-defined option with default configuration
    */
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
                    $base[$key] = self::merge_options($base[$key], $append[$key]);
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
    
    /*
    add debug infos
    */
    function log($str)
    {
        if (empty(self::$debug_info)) {
            self::$debug_info = "/* --------------------------------------------------------------\n\n";
            self::$debug_info .= "                            Debug Infos\n\n";
        }
        self::$debug_info .= $str . "\n";
    }
    
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
    
    /*
    Find need less files
    */
    function collect_lessfiles()
    {
        self::$less_files['mixins'] = self::rglob(self::$conf['mixins_path'] . '/*.less');
        self::$less_files['user']   = self::$f;
        
        // explode less files
        foreach (self::$less_files as $key => $less_files) {
            foreach ((array) $less_files as $f) {
                if (file_exists($f)) {
                    self::$input .= file::get($key, $f);
                }
            }
        }
        return $this;
    }
    
    private static function clean_path($p) {
    	$p = str_replace(array('\\', '//'), '/', $p);
    	return $p;
    }
    
    /*
    generated every useful paths
    */
    function generate_paths()
    {
    	self::$conf['base_path'] = self::clean_path(dirname(__FILE__)).'/';
    	self::$conf['mixins_path'] = self::$conf['base_path'].self::$conf['mixins_path'];
    	self::$f = self::clean_path($_SERVER['DOCUMENT_ROOT'].self::$f);
    	self::$conf['filecache_path'] = self::$conf['cache_path'] .'/'. str_replace(array(
            $_SERVER['DOCUMENT_ROOT'],
            basename(self::$f)
        ), array(
            ''
        ), self::$f);
        self::$cached_f = self::$conf['filecache_path'] . str_replace('.less', '.css', basename(self::$f)); // target main cached css
    }
    
    /*
    let the magic ! less takes care of everything
    */
    function less_to_css()
    {
        self::$less             = new lessc(); // instantiate Less
        self::$less->importDir  = self::$conf['less_options']['importDir']; // define import Directories
        self::$less->debug_info = self::$conf['less_options']['use_fireless']; // use fireless or not
        
        self::$output = self::$less->parse(self::$input); // parse the less file
        
        if (self::$conf['use_compression']) {
            $CSSC       = new CSSCompression(self::$output, self::$conf['compression_options']);
            self::$output = $CSSC->css;
        }
        
        if (self::$conf['debug_info']) {
            self::log("   Parsed files :\n");
            foreach (self::$less->allParsedFiles() as $key => $f) {
                self::log("   * {$key}");
                if ($mixin = in_array(str_replace('\\', '/', $key), self::$less_files['mixins'])) {
                    self::log("     type : auto-imported mixin");
                } else if ($f['parent'] != null) {
                    self::log("     type : user-imported less file");
                    self::log("     imported by : {$f['parent']}");
                } else {
                    self::log("     type : main less file");
                }
                self::log("     last modification : " . date(DATE_RFC822, $f['filemtime']));
                self::log("     next recache : " . date(DATE_RFC822, $f['filemtime'] + self::$conf['cachetime']) . "\n");
            }
            self::$debug_info .= "-------------------------------------------------------------- */\n";
            self::$output = self::$debug_info . self::$output;
        }
        return $this;
    }
    
    /**
     * Return the CSS
     *
     * @param $output What to display
     * @return void
     */
    public static function render_css($level = false) {
        $length   = strlen(self::$output);
        $modified = (self::$conf['in_production'] === true) ? file::modified(self::$cached_f) : file::modified(self::$f);
        $lifetime = (self::$conf['in_production'] === true) ? self::$conf['cachetime'] : 0;
        
        headers::generate($modified, $lifetime, $length);
        // gzip, zlib handler
        self::$output = headers::set_compression(self::$output, $level);
        
        # Send the headers
        headers::send();
        echo self::$output;
        exit;
    }
    
    /**
     * Renders the CSS
     *
     * @param $output What to display
     * @return void
     */
    function format_css()
    {        
        if (self::$conf['in_production'] === true) {
            $path = '';
            foreach (explode('/', self::$conf['filecache_path']) as $folder) {
                if ($folder != '' && !file_exists($path . $folder)) {
                    mkdir($path . $folder, 0777);
                }
                $path .= $folder . '/';
            }
            file_put_contents(self::$cached_f, self::$output);
        }
        else {
            if (file_exists(self::$cached_f)) {
                unlink(self::$cached_f);
            }
        }
        return $this;
    }
    
    function __construct($f)
    {
        self::$f = $f;
        require 'lessphp/lessc.inc.php';
        
        // auto include extends
        $extends = self::rglob('extends/*.inc');
        
        foreach ($extends as $extend) {
            require $extend;
        }
        $yaml = new Yaml();
        $conf = $yaml->load('config.yml');

        try {
            self::$conf    = self::merge_options(self::$conf, $conf); // make conf usable by all methods
            
            // dev mode
            self::$conf['use_compression'] = (self::$conf['in_production']) === true ? self::$conf['use_compression'] : false;
            self::$conf['debug_info']      = (self::$conf['in_production']) === true ? false : self::$conf['debug_info'];

            self::generate_paths(); // generate path config
            if (file::need_to_recache()) {
            	LessCacheer::$conf['less_options']['importDir'][] = dirname(self::$f).'/';
                self::log("   Just recached !\n");
                self::collect_lessfiles() // include every less you take !
                     ->less_to_css() // convert less to css
                     ->format_css() // last css formats
                     ->render_css(); // print the final css
            } else {
                self::$output = file::get_contents(self::$cached_f);
                self::render_css(); // print the cached css
            }
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