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
    public static $cached_f = ''; // requested cached file
    public static $input = '';
    public static $output = '';
    public static $lessfiles = '';
    public static $parsed_css = '';
    public static $less_files = array(); // loaded less files
    public static $less; // less object
    public static $debug_info = null;
    public $headers;
    public $conf = array('install_path' => '', 'mixins_path' => 'lessphp/mixins', 'cache_path' => 'cache', 'debug_info' => true, // display original line and less files within Fireless addons for Firefox
        'in_production' => true, 'cachetime' => 1314000, 'use_compression' => false, 'less_options' => array('importDir' => array()), 'compression_options' => array(
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
    
    /*
    add debug infos
    */
    function log($str)
    {
        if (empty($this->debug_info)) {
            $this->debug_info = "/* --------------------------------------------------------------\n\n";
            $this->debug_info .= "                            Debug Infos\n\n";
        }
        $this->debug_info .= $str . "\n";
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
        $this->less_files['mixins'] = self::rglob($this->conf['base_path'] . $this->conf['mixins_path'] . '/*.less');
        $this->less_files['user']   = $this->f;
        
        // explode less files
        foreach ($this->less_files as $key => $less_files) {
            foreach ((array) $less_files as $f) {
                if (file_exists($f)) {
                    $this->input .= file::get($key, $f);
                }
            }
        }
        return $this;
    }
    
    /*
    generated every useful paths
    */
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
        $this->conf['folder_install'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->conf['base_path']);
        
        $this->conf['origin_install'] = (in_array($this->conf['folder_install'], array(
            '/',
            ''
        ))) ? $this->conf['base_path'] : str_replace($this->conf['folder_install'], '', $this->conf['base_path']);
        
        $this->f = $this->conf['origin_install'] . $this->f; // this less files !
        
        // target cached path
        $this->conf['filecache_path'] = $this->conf['cache_path'] . str_replace(array(
            $this->conf['origin_install'],
            basename($this->f)
        ), array(
            $this->conf['base_url'] . $this->conf['install_path'],
            ''
        ), $this->f);
        $this->cached_f               = $this->conf['filecache_path'] . str_replace('.less', '.css', basename($this->f)); // target main cached css
        
        // assign site_url
        $this->conf['site_url'] = 'http://';
        $this->conf['site_url'] .= $_SERVER['HTTP_HOST'];
        $this->conf['site_url'] = str_replace(':' . $_SERVER['SERVER_PORT'], '', $this->conf['site_url']); // remove port from HTTP_HOST  
        $this->conf['site_url'] .= $this->conf['base_url'];
    }
    
    /*
    let the magic ! less takes care of everything
    */
    function less_to_css()
    {
        $this->less             = new lessc(); // instantiate Less
        $this->less->importDir  = $this->conf['less_options']['importDir']; // define import Directories
        $this->less->debug_info = $this->conf['debug_info'];
        $this->less->addParsedFile($this->f);
        
        $this->output = $this->less->parse($this->input); // parse the less file
        
        if ($this->conf['use_compression']) {
            $CSSC       = new CSSCompression($this->output, $this->conf['compression_options']);
            $this->output = $CSSC->css;
        }
        
        if ($this->conf['debug_info']) {
            $this->log("   Parsed files :\n");
            foreach ($this->less->allParsedFiles() as $key => $f) {
                $this->log("   * {$key}");
                if ($mixin = in_array(str_replace('\\', '/', $key), $this->less_files['mixins'])) {
                    $this->log("     type : auto-imported mixin");
                } else if ($f['parent'] != null) {
                    $this->log("     type : user-imported less file");
                    $this->log("     imported by : {$f['parent']}");
                } else {
                    $this->log("     type : main less file");
                }
                $this->log("     last modification : " . date(DATE_RFC822, $f['filemtime']));
                $this->log("     next recache : " . date(DATE_RFC822, $f['filemtime'] + $this->conf['cachetime']) . "\n");
            }
            $this->debug_info .= "-------------------------------------------------------------- */\n";
            $this->output = $this->debug_info . $this->output;
        }
        return $this;
    }
    
    /**
     * Return the CSS
     *
     * @param $output What to display
     * @return void
     */
    function render_css($level = false) {
        $length   = strlen($this->output);
        $modified = ($this->conf['in_production'] === true) ? file::modified($this->cached_f) : file::modified($this->f);
        $lifetime = ($this->conf['in_production'] === true) ? $this->conf['cachetime'] : 0;
        
        headers::generate($modified, $lifetime, $length);
        // gzip, zlib handler
        $this->output = headers::set_compression($this->output, $level);
        
        # Send the headers
        headers::send();
        echo $this->output;
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
        if ($this->conf['in_production'] === true) {
            $path = '';
            foreach (explode('/', $this->conf['filecache_path']) as $folder) {
                if ($folder != '' && !file_exists($path . $folder)) {
                    mkdir($path . $folder, 0777);
                }
                $path .= $folder . '/';
            }
            file_put_contents($this->cached_f, $this->output);
        }
        else {
            if (file_exists($this->cached_f)) {
                unlink($this->cached_f);
            }
        }
        return $this;
    }
    
    function __construct($f)
    {
        $this->f = $f;
        require('config.inc.php');
        require 'lessphp/lessc.inc.php';
        
        // auto include extends
        $extends = self::rglob('extends/*.inc');
        foreach ($extends as $extend) {
            require $extend;
        }
        
        try {
            $this->recache = false; // init of recache
            $this->conf    = $this->merge_options($this->conf, $conf); // make conf usable by all methods
            
            // dev mode
            $this->conf['use_compression'] = ($this->conf['in_production']) === true ? $this->conf['use_compression'] : false;
            $this->conf['debug_info']      = ($this->conf['in_production']) === true ? false : $this->conf['debug_info'];
            
            $this->generate_paths(); // generate path config
            if (file::need_to_recache()) {
                $this->log("   Just recached !\n");
                $this->collect_lessfiles() // include every less you take !
                     ->less_to_css() // convert less to css
                     ->format_css() // last css formats
                     ->render_css(); // print the final css
            } else {
                file::get_contents($this->cached_f)
                    ->render_css(); // print the cached css
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