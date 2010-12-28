# LessCacheer v1.0.0
#### <http://clearideaz.com>

LessCacheer is an open-source CSS Authoring Framework based. LessCacheer uses [LessPHP](http://leafo.net/lessphp/).

## Features

1. No presentational classes, LessCacheer uses the power of less mixins
2. Grid system based on 960 Grid System
3. Minify CSS with the [CSS Compressor](http://www.codenothing.com/css-compressor/) developed by [Codenothing](http://www.codenothing.com)
4. YAML based configuration
5. Cache the generated CSS files with the correct caching headers (ETags, expires header)

### How to use in your php project

Requests to CSS files are made through LessCacheer:

``<link href="LessCacheer/index.php?f=/css/global.css" rel="stylesheet" />``

Copy LessCacheer into your project.

Create a `config.yml` file inside the LessCacheer folder based on the `config.sample.yml` file.

That's all folk !

### Debugging with FireLess

LessCacheer give you a new tool to work with Firebug : Fireless !

FireLess is a Firebug extension that makes Firebug display the Less filenames and line numbers of LessPHP-generated CSS styles rather than those of the generated CSS. This is an adaptation of the [Firesass extension](https://github.com/nex3/firesass) developped by [Nex3](https://github.com/nex3/firesass).

First, [install FireLess](https://addons.mozilla.org/fr/firefox/addon/259377/).
Second, enable `use_fireless` inside the `config.yml`.