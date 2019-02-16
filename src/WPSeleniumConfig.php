<?php

namespace WPSelenium;
use WPSelenium\Utilities\Logger;

class WPSeleniumConfig{

    private $parsedConfig;
    private $wpSeleniumPathDir;
    private static $instance = null;


    function __construct($configFilePathilePath, $wpSeleniumPathDir)
    {
        $this->wpSeleniumPathDir = $wpSeleniumPathDir;
        $this->parsedConfig=simplexml_load_file($configFilePathilePath);

        if ($this->parsedConfig === false){
            Logger::ERROR("Failed parsing the wpselenium xml file. Please make use it is in the correct format ");
        }
        self::$instance = $this; 
    }

    static function Get(){
        if (self::$instance == null){
            Logger::ERROR("Hmm...Thats strange. Are you tring to access the wpselenium config object without configuring it first");
        }
        return self::$instance ;
    }

    public function GetSiteURL(){
        return $this->parsedConfig->siteUrl;
    }

    public function GetSitePath(){
        return $this->parsedConfig->sitePath;
    }

    public function GetTestDirectory(){
        return $this->parsedConfig->testDirectory;
    }

    public function GetWPTestUsername(){
        return $this->parsedConfig->wpTestUsername;
    }

    public function GetWPTestPassowrd(){
        return $this->parsedConfig->wpTestUserPassword;
    }

    public function GetPhpUnitConfig(){

    }

    public function GetWPSeleniumDir(){
        return $this->wpSeleniumPathDir;
    }
}