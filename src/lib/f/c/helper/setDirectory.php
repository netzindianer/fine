<?php

class f_c_helper_setDirectory
{

    /**
     * do exist directories from file path
     * if not create them
     * 
     * @param string $dirname
     * @param int $mode
     * @param boolean $recursive
     * @return boolean
     */
    public static function helper($dirname, $mode = 0775, $recursive = true)
    {
        if (!file_exists($dirname)) {
            return mkdir($dirname, $mode, $recursive);
        }
        
        return true;
    }
    
}