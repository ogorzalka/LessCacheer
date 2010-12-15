<?php
Class file extends LessCacheer
{
    /**
     * Returns the last modified date of a cache file
     *
     * @param $file
     * @return int
     */
    function modified($file)
    {
        return (file_exists($file)) ? (int) filemtime($file) : 0;
    }
    
    function get($type = 'user', $input, $force_recache = false)
    {
        $basename        = basename($input); // filename

        if ($type == 'mixins') {
            if (!in_array(dirname($input).'/', $this->conf['less_options']['importDir'])) {
                $this->conf['less_options']['importDir'][] = dirname($input).'/';
            }
            $data                                      = "@import '$basename';";
        } else {
            $data = file_get_contents($input);
        }
        return $data;
    }
    
    function need_to_recache() {
        return (
            !$this->conf['in_production'] ||
            (!file_exists($this->cached_f) || file::modified($this->f) > file::modified($this->cached_f)) && $this->conf['in_production']
        );
    }
    
    function get_contents($path) {
        $this->output = file_get_contents($path);
        return $this;
    }
}