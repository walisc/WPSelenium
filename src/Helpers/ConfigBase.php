<?php


namespace WPSelenium\Helpers;
use WPSelenium\Utilities\Logger;

class ConfigBase{

    protected static $instance;
    protected $wpSeleniumConfig;

    function __construct($wpSeleniumConfig)
    {
        $this->wpSeleniumConfig = $wpSeleniumConfig;
        self::$instance = $this;
    }

    static function Get(){
        if (self::$instance == null){
            Logger::ERROR("Helper Config file for teh type your are testing not yet configured", true);
        }
        return self::$instance ;
    }

    public function GetWpSeleniumConfig(){
        return $this->wpSeleniumConfig;
    }
}