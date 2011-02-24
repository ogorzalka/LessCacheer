<?php
class headers
{
    /**
     * Adds a new HTTP header for sending later.
     *
     * @author your name
     * @param $name
     * @param $value
     * @return boolean
     */
    public static function set($name, $value)
    {
        return LessCacheer::$headers[$name] = $value;
    }
    
    /**
     * Sends all of the stored headers to the browser
     *
     * @return void
     */
    public static function send()
    {
        if (!headers_sent()) {
            LessCacheer::$headers = array_unique(LessCacheer::$headers);
            
            foreach (LessCacheer::$headers as $name => $value) {
                if ($name != '_status') {
                    header($name . ':' . $value);
                } //$name != '_status'
                else {
                    if ($value === 304) {
                        header('Status: 304 Not Modified', TRUE, 304);
                    } //$value === 304
                    elseif ($value === 500) {
                        header('HTTP/1.1 500 Internal Server Error');
                    } //$value === 500
                }
            }
        }
    }
    
    public static function set_compression($output, $level)
    {
        if ($level AND ini_get('output_handler') !== 'ob_gzhandler' AND (int) ini_get('zlib.output_compression') === 0) {
            if ($level < 1 OR $level > 9) {
                # Normalize the level to be an integer between 1 and 9. This
                # step must be done to prevent gzencode from triggering an error
                $level = max(1, min($level, 9));
            }
            
            if (stripos(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) {
                $compress = 'gzip';
            }
            elseif (stripos(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== FALSE) {
                $compress = 'deflate';
            }
        }
        
        if (isset($compress) AND $level > 0) {
            switch ($compress) {
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
            self::set('Vary', 'Accept-Encoding');
            
            # Send the content encoding header
            self::set('Content-Encoding', $compress);
        } 
        return $output;
    }
    
    /**
     * Sets the HTTP headers for a particular file
     *
     * @param $param
     * @return return type
     */
    public static function generate($modified, $lifetime, $length)
    {
        LessCacheer::$headers = array();
        
        /**
         * Set the expires headers
         */
        $now = $expires = time();
        
        // Set the expiration timestamp
        $expires += $lifetime;
        
        self::set('Last-Modified', gmdate('D, d M Y H:i:s T', $now));
        self::set('Expires', gmdate('D, d M Y H:i:s T', $expires));
        self::set('Cache-Control', 'max-age=' . $lifetime);
        
        /**
         * Further caching headers
         */
        self::set('ETag', md5(serialize(array(
            $length,
            $modified
        ))));
        self::set('Content-Type', 'text/css');
        
        /**
         * Content Length
         * Sending Content-Length in CGI can result in unexpected behavior
         */
        if (stripos(PHP_SAPI, 'cgi') === FALSE) {
            self::set('Content-Length', $length);
        }
        
        /**
         * Set the expiration headers
         */
        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            if (($strpos = strpos($_SERVER['HTTP_IF_MODIFIED_SINCE'], ';')) !== FALSE) {
                // IE6 and perhaps other IE versions send length too, compensate here
                $mod_time = substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 0, $strpos);
            }
            else {
                $mod_time = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
            }
            
            $mod_time      = strtotime($mod_time);
            $mod_time_diff = $mod_time + $lifetime - time();
            
            if ($mod_time_diff > 0) {
                // Re-send headers
                self::set('Last-Modified', gmdate('D, d M Y H:i:s T', $mod_time));
                self::set('Expires', gmdate('D, d M Y H:i:s T', time() + $mod_time_diff));
                self::set('Cache-Control', 'max-age=' . $mod_time_diff);
                self::set('_status', 304);
                
                // Prevent any output
                LessCacheer::$output = '';
            }
        }
    }
    
    function __construct()
    {
    }
}