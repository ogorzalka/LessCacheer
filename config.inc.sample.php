<?php
$conf = array(
    'css_dir' => '', // css directory (ex: css/ )
    'install_path' => 'modeles/lessphp', // install directory (ex: lessphp)
    'mixins_path' => 'lessphp/mixins', // folder wich contain useful less files
    'cache_mixins' => true,
    'in_production' => false,
    'debug_info' => false,
    'cachetime' => 3600,
    'use_compression' => true,
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
		'readability' => 3,
    )
);