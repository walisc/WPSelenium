<?php

namespace WPSelenium;
use WPSelenium\Helpers\HelperRegistry;
use WPSelenium\Utilities\Logger;
use WPSelenium\Utilities\CONSTS;
use WPSelenium\Utilities\Utilities;
use WPSelenium\ArgsParser;
use WPSelenium\Utilities\Requests;

class WPSeleniumConfig{

    private $parsedConfig;
    private $wpSeleniumPathDir;
    private $wpSeleniumPathWorkingDir;
    private $wpSeleniumProvisionConfig;
    private $selectedBrowserDriver;
    private $phpUnitPath;
    private $phpUnitConfig;
    private $phpUnitConfigPath;
    private $configFilePathFilePath;
    private $testFiles;
    private $helper;

    private static $instance = null;


    function __construct(
        $configFilePathFilePath, $wpSeleniumPathDir)
    {
        $this->configFilePathFilePath = $configFilePathFilePath;
        $this->wpSeleniumPathWorkingDir = dirname($configFilePathFilePath);
        $this->wpSeleniumPathDir = $wpSeleniumPathDir;


        $this->phpUnitPath = sprintf("%s%s%s%s%s%s%s", dirname($configFilePathFilePath), DIRECTORY_SEPARATOR, "vendor", DIRECTORY_SEPARATOR, "bin", DIRECTORY_SEPARATOR, "phpunit" );
        $this->phpUnitConfigPath = sprintf("%s%s%s", dirname($configFilePathFilePath), DIRECTORY_SEPARATOR, "phpunit.xml");
        $this->parsedConfig=simplexml_load_file($configFilePathFilePath);
        $this->parsedArgs = (new ArgsParser())->GetOpts();
        Logger::SetLoglevel($this->parsedArgs->getOption('loglevel', true));
        $this->ConfigParse();
        $this->SetUpPhpUintTests();
        $this->SetUpHelper();
        self::$instance = $this; 
    }

    private function SetUpHelper(){
        $this->helper = HelperRegistry::GetHelper($this->parsedArgs->getOption('type'));
    }

    private function ConfigParse(){
        $this->wpSeleniumProvisionConfig = new ProvisionSeleniumConfig($this->parsedConfig);


        if ($this->parsedConfig === false){
            Logger::ERROR("Failed parsing the wpselenium xml file. Please make use it is in the correct format ", true);
        }

        if (!$this->parsedArgs->getOperand('browser'))
        {
            Logger::ERROR(sprintf("Please specify the browser you want to test on. Available browser drivers:- %s", implode(", ", $this->wpSeleniumProvisionConfig->GetAvailableDrivers())), true);
        }
        else{
            if (!in_array($this->parsedArgs->getOperand('browser'),  $this->wpSeleniumProvisionConfig->GetAvailableDrivers())){
                Logger::ERROR(sprintf("The drivers for the browser you are trying to test for do not exist. Available browser drivers:- %s", implode(", ", $this->wpSeleniumProvisionConfig->GetAvailableDrivers())), true);
            }
            else{
                $this->selectedBrowserDriver = $this->parsedArgs->getOperand('browser');
            }

            if(!Requests::SiteUp($this->GetSiteURL())){
                Logger::ERROR(sprintf("This site you are trying to test seems to be down/offline. Cannot continue with the selenium tests if this is the case. Site URL:- %s", $this->GetSiteURL()), true);
            }
        }
        
    }

    private function SetUpPhpUintTests(){
        $phpunitConfigPath = $this->GetPhpUnitConfigPath();
        
        if (file_exists($phpunitConfigPath)){
            $phpUnitConfig = simplexml_load_file($phpunitConfigPath);
            $isSample = array_key_exists(CONSTS::IS_PHPUNIT_SAMPLE_CONFIG, json_decode(json_encode($phpUnitConfig->attributes()),TRUE));
            $this->phpUnitConfig = [ "isSample" => $isSample,
                                     "config" => $phpUnitConfig];
        }
        else if (empty($this->parsedConfig->phpunit)){
            $this->phpUnitConfig = [ "isSample" => true,
                                     "config" => simplexml_load_file(sprintf("%s%s%s%s%s%s%s", $this->wpSeleniumPathDir, DIRECTORY_SEPARATOR, "src", DIRECTORY_SEPARATOR, "Sample", DIRECTORY_SEPARATOR, "sample_phpunit.config.xml"))];
        }else{
            $this->phpUnitConfig = [ "isSample" => false,
                                     "config" => $this->parsedConfig->phpunit];
        }

        $this->testFiles = $this->ParseTestDirectories(dirname($this->configFilePathFilePath));
    }

    static function Get(){
        if (self::$instance == null){
            Logger::ERROR("Hmm...Thats strange. Are you tring to access the wpselenium config object without configuring it first", true);
        }
        return self::$instance ;
    }

    public function GetHelper(){
        return $this->helper;
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

        $testSuites = get_object_vars( $this->phpUnitConfig["config"]->testsuites);
        
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
            CONSTS::WPSELENIUM_TEMP_TEST_DIR_KEY => $testDirectories,
            CONSTS::WPSELENIUM_TEMP_TEST_FILE_KEY => $testFiles
        ];
    }

    public function GetPhpUnitConfig(){
        return $this->phpUnitConfig;
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

    public function GetWPSeleniumWorkingDir(){
        return $this->wpSeleniumPathWorkingDir;
    }


    public function GetWPSeleniumProvisionConfig(){
        return $this->wpSeleniumProvisionConfig;
    }

    public function GetBroswerDriver(){
        return $this->selectedBrowserDriver;
    }

    public static function GetBinDirectory(){

        $binDirectory = sprintf("%s%s..%s%s", __DIR__, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, "bin");
        if (!file_exists($binDirectory)){ mkdir($binDirectory);}
        return $binDirectory;
    }

    public static function GetTempDirectory(){
        $tempDirectory = sprintf("%s%s..%s%s", __DIR__ ,  DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, "temp");
        if (!file_exists($tempDirectory)){ mkdir($tempDirectory);}
        return $tempDirectory;
    }

    public function GetSeleniumServerPath(){
        return sprintf("%s%s%s", $this->GetBinDirectory() , DIRECTORY_SEPARATOR, "seleniumServer.jar");
    }
    public function GetSeleniumCompressedDriverPath(){
        return  sprintf("%s%s%s", $this->GetBinDirectory() , DIRECTORY_SEPARATOR, sprintf("%sDriverCompressed",  $this->selectedBrowserDriver ));
    }

    public function GetSeleniumRunPort(){
        return $this->parsedConfig->wpSeleniumPort ?? 4444;
    }

    public function GetSeleniumRunCommand(){

        switch(Utilities::GetOS()){
            case "linux":
                return sprintf("java -jar %s -role node -servlet org.openqa.grid.web.servlet.LifecycleServlet -registerCycle 0 -port %d  >  %s%sseleniumLog.log 2>&1 &",$this->GetSeleniumServerPath(), $this->GetSeleniumRunPort(), dirname($this->configFilePathFilePath), DIRECTORY_SEPARATOR );
            case "win":
                return sprintf("java -jar %s -role node -servlet org.openqa.grid.web.servlet.LifecycleServlet -registerCycle 0 -port %d  > %s%sseleniumLog.log 2>&1 ", $this->GetSeleniumServerPath(), $this->GetSeleniumRunPort(), dirname($this->configFilePathFilePath), DIRECTORY_SEPARATOR );
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