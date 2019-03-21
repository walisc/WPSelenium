<?php

namespace WPSelenium;
use WPSelenium\Utilities\Logger;
use WPSelenium\Utilities\CONSTS;
use WPSelenium\Utilities\Utilities;

class WPSeleniumConfig{

    private $parsedConfig;
    private $wpSeleniumPathDir;
    private $wpSeleniumProvisionConfig;
    private $selectedBrowserDriver;
    private $binDirectory;
    private $phpUnitPath;
    private $phpUnitConfigPath;
    private $testFiles;
    private static $instance = null;


    function __construct($configFilePathilePath, $wpSeleniumPathDir, $argc, $argv)
    {
        $this->wpSeleniumPathDir = $wpSeleniumPathDir;
        $this->binDirectory = sprintf("%s%s%s", $this->wpSeleniumPathDir, DIRECTORY_SEPARATOR, "bin");
        $this->phpUnitPath = sprintf("%s%s%s%s%s%s%s", dirname($configFilePathilePath), DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "bin", DIRECTORY_SEPARATOR, "phpunit" );
        $this->phpUnitConfigPath = sprintf("%s%s%s", dirname($configFilePathilePath), DIRECTORY_SEPARATOR, "phpunit.xml");
        $this->parsedConfig=simplexml_load_file($configFilePathilePath);
        $this->testFiles = $this->ParseTestDirectories(dirname($configFilePathilePath));

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

    public function GetTestFiles(){
        return $this->testFiles;
    }
    public function ParseTestDirectories($workingDir=""){

        $testDirectories = [];
        $testFiles = [];

        function GetTestsFiles($type, $testSuiteObj, &$testDirectories)  {
            if (array_key_exists($type, $testSuiteObj)){
                if (is_array($testSuiteObj[$type]))
                {
                    $testDirectories = array_merge($testDirectories, $testSuiteObj[$type]);
                }
                else{
                    array_push($testDirectories,$testSuiteObj[$type] );
                }
            }   
        }

        $testSuites = get_object_vars( $this->parsedConfig->phpunit->testsuites)['testsuite'];
        
        if (is_array($testSuites))
        {
            foreach( $testSuites as $key => $value)
            {
                $testSuiteObj = get_object_vars($value);
                GetTestsFiles('directory',$testSuiteObj, $testDirectories);
                GetTestsFiles('file',$testSuiteObj, $testFiles);
            }
        }
        
        foreach($testDirectories as &$testDirectory){
            $testDirectory = sprintf("%s%s%s", $workingDir, DIRECTORY_SEPARATOR, $testDirectory);
        }
        foreach($testFiles as &$testFile){
            $testFile = sprintf("%s%s%s", $workingDir, DIRECTORY_SEPARATOR, $testFile);
        }

        return [
            [CONSTS::WPSELENIUM_TEMP_TEST_DIR_KEY] => $testDirectories,
            [CONSTS::WPSELENIUM_TEMP_TEST_FILE_KEY] => $testFiles
        ];
    }

    public function GetWPTestUsername(){
        return $this->parsedConfig->wpTestUsername;
    }

    public function GetWPTestPassowrd(){
        return $this->parsedConfig->wpTestUserPassword;
    }

    public function GetPhpUnitConfig(){
        return $this->parsedConfig->phpunit;
    }

    public function GetPhpUnitConfigPath(){
        return $this->phpUnitConfigPath;
    }

    public function GetPhpUnitPath(){
        return $this->phpUnitPath;
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

    public function GetSeleniumRunPort(){
        return $this->parsedConfig->wpSeleniumPort ?? 4444;
    }

    public function GetSeleniumRunCommand(){

        switch(Utilities::GetOS()){
            case "linux":
                return sprintf("java -jar %s -role node -servlet org.openqa.grid.web.servlet.LifecycleServlet -registerCycle 0 -port %d  >  %s%sseleniumLog.log 2>&1 &",$this->GetSeleniumServerPath(), $this->GetSeleniumRunPort(), $this->wpSeleniumPathDir, DIRECTORY_SEPARATOR );
            case "win":
                return sprintf("start /b java -jar %s -role node -servlet org.openqa.grid.web.servlet.LifecycleServlet -registerCycle 0 -port %d  > %s%sseleniumLog.log 2>&1 ", $this->GetSeleniumServerPath(), $this->GetSeleniumRunPort(), $this->wpSeleniumPathDir, DIRECTORY_SEPARATOR );
        }

    }
}

class ProvisionSeleniumConfig{
    
    private $librarySeleniumProvisionConfig;
    private $userSeleniumProvisionConfig;
    private $availableDrivers;
    private $combinedSeleniumProvisionConfig;

    private function ConvertToArray($xml){
        $json = json_encode($xml);
        return json_decode($json,TRUE);
    }
    
    // From https://medium.com/@kcmueller/php-merging-two-multi-dimensional-arrays-overwriting-existing-values-8648d2a7ea4f
    private function array_merge_recursive_distinct(array &$array1, array &$array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }


    function __construct($wpSeleniumConfig)
    {
        $this->librarySeleniumProvisionConfig = $this->ConvertToArray($this->parsedConfig=simplexml_load_file(sprintf("%s%s%s%s%s", __DIR__, DIRECTORY_SEPARATOR, "Provision", DIRECTORY_SEPARATOR, "wpseleniumprovision.xml")));    
        $this->userSeleniumProvisionConfig =  $this->ConvertToArray($wpSeleniumConfig->wpSeleniumProvision);

        $this->combinedSeleniumProvisionConfig = $this->array_merge_recursive_distinct($this->librarySeleniumProvisionConfig,$this->userSeleniumProvisionConfig);
        $this->availableDrivers = array_keys($this->combinedSeleniumProvisionConfig['driverUrl'][Utilities::GetOS()]);
  
    }

    function GetAvailableDrivers(){
        return $this->availableDrivers;
    }

    function GetSeleniumDownloadUrl(){ 
        return $this->combinedSeleniumProvisionConfig['wpSeleniumUrl'];
    }

    function GetDriverDownloadUrl($selectedBrowserDriver){
        return $this->combinedSeleniumProvisionConfig['driverUrl'][Utilities::GetOS()][$selectedBrowserDriver];
    }
}