<?php

namespace WPSelenium\Utilities;

Class Logger{

    static function INFO($msg){
        echo sprintf("\n\033[32m %s INFO:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg); 
    }
    static function WARN($msg){
        echo sprintf("\n\033[33m %s WARN:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg); 
    }
    static function ERROR($msg){
        echo sprintf("\n\033[31m %s ERROR:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg);     
    }
    static function FATAL($msg){
        echo sprintf("\n\033[31m %s FATAL:\033[0m %s\n",  date("Y-m-d H:i:s"), $msg);     
    }

}