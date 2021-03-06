<?php

namespace WPSelenium;
use WPSelenium\Utilities\Logger;
use WPSelenium\Utilities\Requests;
use WPSelenium\Utilities\CONSTS;
use WPSelenium\Utilities\Utilities;

class TestRunner{

    private $wpSeleniumConfig;

    public static function RunTests(){
        (new self());
    }

    function __construct()
    {
        $this->wpSeleniumConfig = WPSeleniumConfig::Get();
        $this->wpTempTestsJsonPath = sprintf("%s%s%s", $this->wpSeleniumConfig->GetTempDirectory(), DIRECTORY_SEPARATOR, CONSTS::WPSELENIUM_TEMP_TEST_FILE);

        $fp = fopen($this->wpTempTestsJsonPath, 'w');
        fwrite($fp,json_encode( $this->wpSeleniumConfig->GetTestFiles()));
        fclose($fp);
        $this->SetEnvironmentVariables();
        $this->StartSeleniumServer();
        $this->StartWPSeleniumTests();
        $this->CleanUp();
    }

    function SetEnvironmentVariables(){
        Logger::INFO("Setting Environment variables");
        putenv('PATH=' . getenv('PATH') . PATH_SEPARATOR . $this->wpSeleniumConfig->GetBinDirectory());
        putenv('WPSELENIUM_TEST_SITE=' . $this->wpSeleniumConfig->GetSiteURL() );
        putenv('WPSELENIUM_DRIVER=' . $this->wpSeleniumConfig->GetBroswerDriver() );
        putenv('WPSELENIUM_TEST_PORT=' . $this->wpSeleniumConfig->GetSeleniumRunPort() );
        $this->wpSeleniumConfig->GetHelper()->SetEnvVariables();

    }
    function StartSeleniumServer(){

        //adding sleep command to give time for the command to full run/programs exceute
        sleep(1);
        # 3. Starting Selenium
        Logger::INFO("---- Starting Selenium----- \n\n");

        if (Requests::SiteUp("http://localhost:{$this->wpSeleniumConfig->GetSeleniumRunPort()}")){ 
            Requests::Get("http://localhost:{$this->wpSeleniumConfig->GetSeleniumRunPort()}/extra/LifecycleServlet?action=shutdown");
            sleep(1);
        } 
        Utilities::StartProcessInBackground($this->wpSeleniumConfig->GetSeleniumRunCommand());
        sleep(2);
    }

    function StartWPSeleniumTests(){
        $phpunitConfig = $this->wpSeleniumConfig->GetPhpUnitConfig();
        $phpunitConfigPath = $this->wpSeleniumConfig->GetPhpUnitConfigPath();

        if ($phpunitConfig['configType'] == CONSTS::PHPUNIT_CONFIG_TYPE_WPSELENIUM_CONFIG || $phpunitConfig['configType'] == CONSTS::PHPUNIT_CONFIG_TYPE_WPSELENIUM_SAMPLE ){
            $doc = new \DOMDocument("1.0", "ISO-8859-15");
            $doc->formatOutput = TRUE;
            $doc->loadXML(($this->wpSeleniumConfig->GetPhpUnitConfig()["config"])->asXML());
            $doc->saveXML();
            $doc->save( $phpunitConfigPath);
        }
        
        Logger::INFO("---- Running WPSelenium Tests---- \n\n");
        $this->wpSeleniumConfig->GetHelper()->PhpUnitRunner($this->wpSeleniumConfig->GetPhpUnitPath());
        Logger::INFO("---- Completed Running WPSelenium Tests. Shutting Down.---- \n\n");
        sleep(2);
        Requests::Get("http://localhost:{$this->wpSeleniumConfig->GetSeleniumRunPort()}/extra/LifecycleServlet?action=shutdown");
        sleep(2);
        
    }

    function CleanUp(){
        //TODO: Maybe add thise commands to the config
        switch(Utilities::GetOS()){
            case "linux":
                if ($this->wpSeleniumConfig->GetBroswerDriver() == CONSTS::SUPPORTED_DRIVERS_CHROME){
                    exec("if pgrep chromedriver; then pgrep chromedriver | xargs kill -9; fi");
                }
                else if ($this->wpSeleniumConfig->GetBroswerDriver() == CONSTS::SUPPORTED_DRIVERS_FIREFOX){
                    exec("if pgrep geckodriver; then pgrep geckodriver | xargs kill -9; fi");
                }
                break;
            case "win":
                if ($this->wpSeleniumConfig->GetBroswerDriver() == CONSTS::SUPPORTED_DRIVERS_CHROME){
                    exec("taskkill /im chromedriver.exe /f 2>&1 | exit /B 0");
                }
                else if ($this->wpSeleniumConfig->GetBroswerDriver() == CONSTS::SUPPORTED_DRIVERS_FIREFOX){
		    exec("taskkill /im geckodriver.exe /f 2>&1 | exit /B 0");
                }
                break;
        }
    }
    
    
}