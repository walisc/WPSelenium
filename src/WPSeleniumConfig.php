<?php

namespace WPSelenium;
use WPSelenium\Utilities\Logger;

class WPSeleniumConfig{

    private $parsedConfig;
    private $wpSeleniumPathDir;
    private $wpSeleniumProvisionConfig;
    private $selectedBrowserDriver;
    private $binDirectory;
    private static $instance = null;


    function __construct($configFilePathilePath, $wpSeleniumPathDir, $argc, $argv)
    {
        $this->wpSeleniumPathDir = $wpSeleniumPathDir;
        $this->binDirectory = sprintf("%s%s%s", $this->wpSeleniumPathDir, DIRECTORY_SEPARATOR, "bin");
        $this->parsedConfig=simplexml_load_file($configFilePathilePath);


        $this->wpSeleniumProvisionConfig = new ProvisionSeleniumConfig($this->parsedConfig);

        if ($this->parsedConfig === false){
            Logger::ERROR("Failed parsing the wpselenium xml file. Please make use it is in the correct format ", true);
        }

        if ($argc < 2)
        {
            Logger::ERROR(sprintf("Please specify the browser you want to test on. Available browser drivers:- %s", implode(", ", $this->wpSeleniumProvisionConfig->GetAvailableDrivers())), true);
        }
        else{
            if (!in_array($argv[1],  $this->wpSeleniumProvisionConfig->GetAvailableDrivers())){
                Logger::ERROR(sprintf("The drivers for the browser you are trying to test for do not exist. Available browser drivers:- %s", implode(", ", $this->wpSeleniumProvisionConfig->GetAvailableDrivers())), true);
            }
            else{
                $this->selectedBrowserDriver = $argv[1];
            }
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

    public function GetWPSeleniumProvisionConfig(){
        return $this->wpSeleniumProvisionConfig;
    }

    public function GetBroswerDriver(){
        return $this->selectedBrowserDriver;
    }

    public function GetBinDirectory(){
        return $this->binDirectory;
    }

    public function GetSeleniumServerPath(){
        return sprintf("%s%s%s", $this->binDirectory , DIRECTORY_SEPARATOR, "seleniumServer.jar");
    }
    public function GetSeleniumCompressedDriverPath(){
        return  sprintf("%s%s%s", $this->binDirectory , DIRECTORY_SEPARATOR, sprintf("%sDriverCompressed",  $this->selectedBrowserDriver ));
    }
}

class ProvisionSeleniumConfig{
    
    private $librarySeleniumProvisionConfig;
    private $userSeleniumProvisionConfig;
    private $availableDrivers;

    function __construct($wpSeleniumConfig)
    {
        $this->librarySeleniumProvisionConfig = $this->parsedConfig=simplexml_load_file(sprintf("%s%s%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "Provision", DIRECTORY_SEPARATOR, "wpseleniumprovision.xml"));    
        $this->userSeleniumProvisionConfig =  $wpSeleniumConfig->wpSeleniumProvision;
        if( $this->userSeleniumProvisionConfig->driverUrl){
            $driversDetails = array_merge($this->librarySeleniumProvisionConfig->driverUrl->{strtolower(PHP_OS)}, $this->userSeleniumProvisionConfig->driverUrl->{strtolower(PHP_OS)});
        }
        else{
            $driversDetails = $this->librarySeleniumProvisionConfig->driverUrl->{strtolower(PHP_OS)};
        }

        //TODO: merge array
        $this->availableDrivers = array_keys(get_object_vars($driversDetails));
  
    }

    function GetAvailableDrivers(){
        return $this->availableDrivers;
    }

    function GetSeleniumDownloadUrl(){ 
        //TODO: Work on the user config
        if( $this->userSeleniumProvisionConfig->wpSeleniumUrl){
            return $this->userSeleniumProvisionConfig->wpSeleniumUrl;
        }
        else{
            return $this->librarySeleniumProvisionConfig->wpSeleniumUrl;
        }
    }

    function GetDriverDownloadUrl($selectedBrowserDriver){

        //TODO: Work on the user config
        if( $this->userSeleniumProvisionConfig->driverUrl){
            $driversDetails = array_merge($this->librarySeleniumProvisionConfig->driverUrl->{strtolower(PHP_OS)}, $this->userSeleniumProvisionConfig->driverUrl->{strtolower(PHP_OS)});
        }
        else{
            return $this->librarySeleniumProvisionConfig->driverUrl->{strtolower(PHP_OS)}->{$selectedBrowserDriver};
        }
       
    }
}