<?php
Class LessCacheer {
    static $file_cache;
    public $conf;
    /**
    * Any files that are found with find_file are stored here so that
    * any further requestes for the files are just given the path
    * from this array, rather than searching for the file again.
    *
    * @var array
    */
    private static $find_file_paths;
    public static $css = '';
    public static $mixin_cssfile = '';
    public static $parsed_css = '';
    public static $mixin_files = array();
    public static $css_files = array();
    public static $recache;
	public $headers;
	

    /**
    * Include paths
    *
    * These are used for finding files on the system. Rather than
    * using PHP's built-in include paths, we just store the paths
    * in this array and use the find_file function to locate it.
    *
    * @var array
    */
    private static $include_paths = array();
    
    static function rglob($pattern, $flags = 0, $path = '') {
        if (!$path && ($dir = dirname($pattern)) != '.') {
            if ($dir == '\\' || $dir == '/') $dir = '';
            return self::rglob(basename($pattern), $flags, $dir . '/');
        }
        $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
        $files = glob($path . $pattern, $flags);
        if(is_array($paths) && is_array($files)) {
            foreach ($paths as $p) $files = array_merge($files, self::rglob($pattern, $flags, $p . '/'));
        }
        return is_array($files) ? $files : array();
    }
    
    /**
    * Get all include paths.
    *
    * @return  array
    */
    public static function include_paths()
    {
        return self::$include_paths;
    }
    
    /**
    * Adds a path to the include paths list
    *
    * @param     $path     The server path to add
    * @return     void
    */
    public static function add_include_path($path)
    {
        if(func_num_args() > 1)
        {
            $args = func_get_args();
            
            foreach($args as $inc)
            self::add_include_path($inc);
        }
        
        if(is_file($path))
        {
            $path = dirname($path);
        }
        
        if(!in_array($path,self::$include_paths))
        {
            self::$include_paths[] = bCSS_Utils::fix_path($path);
        }
    }
    
    /**
    * Looks for the file recursively in the specified directory.
    * This will also look for _filename to handle Sass partials.
    * @param string filename to look for
    * @param string path to directory to look in and under
    * @return mixed string: full path to file if found, false if not
    */
    function find_file($filename, $dir) {
        $partialname = dirname($filename).DIRECTORY_SEPARATOR.'_'.basename($filename);
        
        foreach (array($filename, $partialname) as $file) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . $file)) {
                return $this->fix_path(realpath($dir . DIRECTORY_SEPARATOR . $file));
            }
        }
        
        $files = array_slice(scandir($dir), 2);
        
        foreach ($files as $file) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                $path = self::find_file($filename, $dir . DIRECTORY_SEPARATOR . $file);
                if ($path !== false) {
                    return $this->fix_path($path);
                }
            }
        } // foreach
        return false;
    }
    
    function make_alias($str) {
        $str = str_replace(
            array($this->conf['base_path'], '.less', '/'),
            array('', '', '_'),
        $str);
        return $str;
    }
    
    function fix_path($path) {
        return str_replace('\\', '/', $path);
    }
    
    function cache($file_location, $is_mixin = false, $force_recache = false) {
        $alias_cache = $this->make_alias($file_location); // path relative to the file
        $basename = basename($file_location);// filename
        
        $cached_filename = DataCache::getFilename($alias_cache, $basename);
        //print_r($this->modified($cached_filename));
        //die('fuck');
        if ($this->modified($cached_filename) < $this->modified($file_location)+$this->conf['cachetime']) {
            @unlink($cached_filename);
            $this->recache = true;
        }
        
        if (($this->conf['cache_mixins'] == false && $is_mixin == true) || ($this->conf['in_production'] == false && $is_mixin == false)) {
            $data = file_get_contents($file_location)."\n";
        }
        else if (!$data = DataCache::Get($alias_cache, $basename)) {
            $data = file_get_contents($file_location)."\n";
            DataCache::Put($alias_cache, $basename, $this->conf['cachetime'], $data); // put data inside the cache
        }
        return $data;
    }
    

	/**
	 * Returns the last modified date of a cache file
	 *
	 * @param $file
	 * @return int
	 */
	function modified($file)
	{
		return ( file_exists($file) ) ? (int) filemtime($file) : 0 ;
	}
    
	/**
	 * Adds a new HTTP header for sending later.
	 *
	 * @author your name
	 * @param $name
	 * @param $value
	 * @return boolean
	 */
    function header($name,$value)
	{
		return $this->headers[$name] = $value;
	}

	/**
	 * Sends all of the stored headers to the browser
	 *
	 * @return void
	 */
	function send_headers()
	{
		if(!headers_sent())
		{
			$this->headers = array_unique($this->headers);

			foreach($this->headers as $name => $value)
			{
				if($name != '_status')
				{
					header($name . ':' . $value);
				}
				else
				{
					if($value === 304)
					{
						header('Status: 304 Not Modified', TRUE, 304);
					}
					elseif($value === 500)
					{
						header('HTTP/1.1 500 Internal Server Error');
					}
				}
			}
		}
	}
	
	/**
	 * Sets the HTTP headers for a particular file
	 *
	 * @param $param
	 * @return return type
	 */
	function set_headers($modified,$lifetime,$length)
	{	
		$this->headers = array();
	
		/**
		 * Set the expires headers
		 */
		$now = $expires = time();

		// Set the expiration timestamp
		$expires += $lifetime;

		$this->header('Last-Modified',gmdate('D, d M Y H:i:s T', $now));
		$this->header('Expires',gmdate('D, d M Y H:i:s T', $expires));
		$this->header('Cache-Control','max-age='.$lifetime);
				
		/**
		 * Further caching headers
		 */
		$this->header('ETag', md5(serialize(array($length,$modified))) );
		$this->header('Content-Type','text/css');
		
		/**
		 * Content Length
		 * Sending Content-Length in CGI can result in unexpected behavior
		 */
		if(stripos(PHP_SAPI, 'cgi') === FALSE)
		{
			$this->header('Content-Length',$length);
		}
		
		/**
		 * Set the expiration headers
		 */
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']))
		{
			if (($strpos = strpos($_SERVER['HTTP_IF_MODIFIED_SINCE'], ';')) !== FALSE)
			{
				// IE6 and perhaps other IE versions send length too, compensate here
				$mod_time = substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 0, $strpos);
			}
			else
			{
				$mod_time = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
			}

			$mod_time = strtotime($mod_time);
			$mod_time_diff = $mod_time + $lifetime - time();

			if ($mod_time_diff > 0)
			{
				// Re-send headers
				$this->header('Last-Modified', gmdate('D, d M Y H:i:s T', $mod_time) );
				$this->header('Expires', gmdate('D, d M Y H:i:s T', time() + $mod_time_diff) );
				$this->header('Cache-Control', 'max-age='.$mod_time_diff);
				$this->header('_status',304);

				// Prevent any output
				$this->output = '';
			}
		}
	}
	
	/**
	 * Renders the CSS
	 *
	 * @param $output What to display
	 * @return void
	 */
	function render_css($output,$level = false)
	{
		if ($level AND ini_get('output_handler') !== 'ob_gzhandler' AND (int) ini_get('zlib.output_compression') === 0)
		{
			if ($level < 1 OR $level > 9)
			{
				# Normalize the level to be an integer between 1 and 9. This
				# step must be done to prevent gzencode from triggering an error
				$level = max(1, min($level, 9));
			}

			if (stripos(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
			{
				$compress = 'gzip';
			}
			elseif (stripos(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== FALSE)
			{
				$compress = 'deflate';
			}
		}

		if (isset($compress) AND $level > 0)
		{
			switch ($compress)
			{
				case 'gzip':
					# Compress output using gzip
					$output = gzencode($output, $level);
				break;
				case 'deflate':
					# Compress output using zlib (HTTP deflate)
					$output = gzdeflate($output, $level);
				break;
			}

			# This header must be sent with compressed content to prevent browser caches from breaking
			$this->header('Vary','Accept-Encoding');

			# Send the content encoding header
			$this->header('Content-Encoding',$compress);
		}
	
		# Send the headers
		$this->send_headers();
	
		echo $output;
		exit;
	}
	
	function generate_paths() {
		// automatically assign base_path and base_url
		    $sapi= 'undefined';
		    if (!strstr($_SERVER['PHP_SELF'], $_SERVER['SCRIPT_NAME']) && ($sapi= @ php_sapi_name()) == 'cgi') {
		        $script_name= $_SERVER['PHP_SELF'];
		    } else {
		        $script_name= $_SERVER['SCRIPT_NAME'];
		    }
		    $a= explode("/".$this->conf['install_path'], str_replace("\\", "/", dirname($script_name)));
		    if (count($a) > 1)
		        array_pop($a);
		    $url= implode($this->conf['install_path'], $a);
		    reset($a);
		    $a= explode($this->conf['install_path'], str_replace("\\", "/", dirname(__FILE__)));
		    if (count($a) > 1)
		        array_pop($a);
		    $pth= implode($this->conf['install_path'], $a);
		    unset ($a);
		    $this->conf['base_url'] = $url . (substr($url, -1) != "/" ? "/" : "");
		    $this->conf['base_path'] = $pth . (substr($pth, -1) != "/" && substr($pth, -1) != "\\" ? "/" : "");
		    $this->conf['origin_path'] = str_replace($this->conf['base_url'], '', $this->conf['base_path']);
		    // assign site_url
		    $this->conf['site_url'] = 'http://';
		    $this->conf['site_url'] .= $_SERVER['HTTP_HOST'];
		    if ($_SERVER['SERVER_PORT'] != 80)
		        $this->conf['site_url'] = str_replace(':' . $_SERVER['SERVER_PORT'], '', $site_url); // remove port from HTTP_HOST  
		    $this->conf['site_url'] .= ':' . $_SERVER['SERVER_PORT'];
		    $this->conf['site_url'] .= $this->conf['base_url'];
	}

    function __construct($f) {
        require ('config.inc.php');
        require 'less/lessc.inc.php';
        require 'helpers/cache/cache.class.php';
        require 'helpers/csscompression/csscompression.class.php';
        
        try {   
            $this->recache = false; // init of recache
        
            
            // additionnal conf
            $conf['script_path'] = $this->fix_path(dirname(__FILE__));

            $this->conf = $conf; // make conf usable by all methods
            $this->generate_paths();
            
            
            // mixins import
            if (!$this->mixin_cssfile = DataCache::Get("mixins", "mixin_cssfile")) {
                $this->mixin_files = self::rglob($this->conf['base_path'].$this->conf['mixins_path'].'/*.less');
                foreach($this->mixin_files as $mixin_file) {
                    $this->css .= $this->cache($mixin_file, true);
                }
            }
            
            // explode css files
            $this->css_files = explode(',', $f);
            
            // foreach css files
            foreach($this->css_files as $css_file) {
            	$requested_css = $this->conf['origin_path'].$css_file;
            	$importDir = dirname($requested_css);
                if (file_exists($requested_css)) {
                    $this->css .= $this->cache($requested_css);
                }
            }
            // return the parsed css
            $cache_name = $this->make_alias($f);
            
            // specify the import dir
            $less_options = array(
                'importDir' => $importDir.'/'
            );
            
            $less = new lessc(); // instantiate Less
            
            // if production mode -> use cache
            if ($this->conf['in_production'] == true) {
                // if compression is ON
                if ($this->conf['use_compression'] != false) {
                    $cache_name = $cache_name.'-'.md5(serialize($this->conf['compression_options']));
                }                

                $cached_filename = DataCache::getFilename($cache_name, 'mainless'); // retrieve the cached filename
                
                // if we need to cache again, we unlink the previous cached file
                if ($this->recache == true) {
                    @unlink($cached_filename);
                }
                
                // if there's no cache file
                if (!$this->parsed_css = DataCache::Get($cache_name, 'mainless')) {
                    $this->parsed_css = $less->parse($this->css, $less_options); // parse the less file
                    if ($this->conf['use_compression'] != false) {
                        $CSSC = new CSSCompression( $this->parsed_css, $this->conf['compression_options']);
                        $this->parsed_css = $CSSC->css;
                    }
                    DataCache::Put($cache_name, 'mainless', $this->conf['cachetime'], $this->parsed_css); // put data inside the cache
                }
                $modified = $this->modified($cached_filename);
            } else { 
                $this->parsed_css = $less->parse($this->css, $less_options);  // parse the less file
                if ($this->conf['use_compression'] != false) {
                    $CSSC = new CSSCompression( $this->parsed_css, $this->conf['compression_options']);
                    $this->parsed_css = $CSSC->css;
                }
                $modified = $this->modified($this->css_files[0]);
            }

			$length = strlen($this->parsed_css);
			
			$lifetime = ($this->conf['in_production'] === true) ? $this->conf['cachetime'] : 0;
			header("Content-type: text/css");
			$this->set_headers($modified,$lifetime,$length);
            $this->render_css($this->parsed_css); // print the final css
        }
        /**
		 * If any errors were encountered
		 */
		catch( Exception $e )
		{
		    $this->header('_status',500);
			/** 
			 * The message returned by the error 
			 */
			$message = $e->getMessage();
			$trace = $e->getTrace();
			$title = $trace[0]['function'];
			$file = $f;
			/** 
			 * Load in the error view
			 */
			//if($this->conf['in_production'] === false)
			//{
				$this->send_headers();

				require 'view/less_error.php';
			//}
		}
    }
    
}
$less = new LessCacheer($_GET['f']);