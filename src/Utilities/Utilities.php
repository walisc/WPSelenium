<?php

namespace WPSelenium\Utilities;

class Utilities{
    public static function GetOS(){
        switch (true) {
            case stristr(PHP_OS, 'DAR'): return "dar";
            case stristr(PHP_OS, 'WIN'): return "win";
            case stristr(PHP_OS, 'LINUX'): return "linux";
            default : return self::OS_UNKNOWN;
        }
    }

    public static function ChmodR($path, $filemode) { 
        if (!is_dir($path)) 
            return chmod($path, $filemode); 
    
        $dh = opendir($path); 
        while (($file = readdir($dh)) !== false) { 
            if($file != '.' && $file != '..') { 
                $fullpath = $path.DIRECTORY_SEPARATOR.$file; 
                if(is_link($fullpath)) 
                    return FALSE; 
                elseif(!is_dir($fullpath)) 
                    if (!chmod($fullpath, $filemode)) 
                        return FALSE; 
                elseif(!self::ChmodR($fullpath, $filemode)) 
                    return FALSE; 
            } 
        } 
    
        closedir($dh); 
    
        if(chmod($path, $filemode)) 
            return TRUE; 
        else 
            return FALSE; 
    } 

    public static function RecursiveCopy($src,$dst) { 
        $dir = opendir($src); 
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . DIRECTORY_SEPARATOR . $file) ) { 
                    self::RecurseCopy($src . DIRECTORY_SEPARATOR . $file,$dst . DIRECTORY_SEPARATOR . $file); 
                } 
                else { 
                    copy($src .DIRECTORY_SEPARATOR . $file,$dst .DIRECTORY_SEPARATOR. $file); 
                } 
            } 
        } 
        closedir($dir); 
    } 
}