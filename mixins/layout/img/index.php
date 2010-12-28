<?php
class bgGrid
{
    public static $cache_target;
    
    public static function find_cache_folder()
    {
        $uri       = explode('mixins/', dirname(__FILE__));
        $cache_dir = $uri[0] . 'cache/grid/';
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir, 0777);
        }
        return $cache_dir;
    }
    
    /**
     * Generates the debug grid background image
     *
     * @author Olivier Gorzalka
     * @param $cc Column count
     * @param $cw Column width
     * @param $gw Gutter Width
     * @return null
     */
    private static function create_grid_image($cc = 1, $cw, $gw)
    {
        $cc = (int) $cc;
        $cw = (int) $cw;
        $bl = 1;
        $gw = (int) $gw;
        
        self::$cache_target = self::find_cache_folder() . "{$cc}col_{$cw}px_{$gw}px_grid.png";
        ;
        
        if (file_exists(self::$cache_target)) {
            $image = ImageCreate(($cw + 2 * $gw) * 6 + $gw, $bl);
            
            $colorColumn = ImageColorAllocate($image, 240, 240, 240);
            $colorGutter = ImageColorAllocate($image, 255, 255, 255);
            
            
            for ($i = 0; $i <= $cc; $i++) {
                $posleft = ($i == 0) ? 0 : ((2 * $gw + $cw + 1) * $i);
                
                # Draw left gutter
                Imagefilledrectangle($image, 0 + $posleft, 0, ($gw - 1) + $posleft, $bl, $colorGutter);
                
                # Draw column
                Imagefilledrectangle($image, $gw + $posleft, 0, ($cw + $gw - 1) + $posleft, $bl, $colorColumn);
                
                # Draw right gutter
                Imagefilledrectangle($image, ($gw + $cw + 1) + $posleft, 0, ($cw + 2 * $gw) + $posleft, $bl, $colorGutter);
            }
            
            ImagePNG($image, self::$cache_target);
            # Kill it
            ImageDestroy($image);
        }
    }
    
    function __construct($params)
    {
        self::create_grid_image($params['cc'], $params['cw'], $params['gw']);
        header('Content-Type: image/png');
        echo file_get_contents(self::$cache_target);
    }
}

$img = new bgGrid($_GET);