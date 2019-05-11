<?php

namespace WPSelenium\Utilities;
use WPSelenium\WPSeleniumConfig;

Class Logger{

    private static $loglevel = "info";
    
    static function SetLoglevel($loglevel){
        return self::$loglevel = strtolower($loglevel);
    }
    static function INFO($msg){
        if(!in_array(self::$loglevel, ["warn", "error", "fatal"]) )
        {
            echo sprintf("\n\033[32m %s INFO:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg); 
        }
    }
    static function WARN($msg){
        if(!in_array(self::$loglevel, ["error", "fatal"]) )
        {
            echo sprintf("\n\033[33m %s WARN:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg); 
        }
    }
    static function ERROR($msg, $shouldExit=false){
        if(!in_array(self::$loglevel, ["fatal"]) )
        {
            echo sprintf("\n\033[31m %s ERROR:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg);   
        }  
        self::Quit ($shouldExit);
    }
    static function FATAL($msg, $shouldExit){
        echo sprintf("\n\033[31m %s FATAL:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg); 
        self::Quit ($shouldExit);    
    }

    static function Quit($shouldExit){
        if ($shouldExit){
            echo("\n\n");
            exit(0);
        }
    }

}