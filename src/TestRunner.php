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

        $fp = fopen(sprintf("%s%s%s", __DIR__, DIRECTORY_SEPARATOR, CONSTS::WPSELENIUM_TEMP_TEST_FILE), 'w');
        fwrite($fp,json_encode( $this->wpSeleniumConfig->GetTestFiles()));
        fclose($fp);
        $this->StartSeleniumServer();
        $this->StartWPSeleniumTests();
        $this->CleanUp();
    }

    function StartSeleniumServer(){
    
        Logger::INFO("Setting Enviroment variables");
        putenv('PATH=' . getenv('PATH') . PATH_SEPARATOR . $this->wpSeleniumConfig->GetBinDirectory());
        putenv('WPSELENIUM_TEST_SITE=' . $this->wpSeleniumConfig->GetSiteURL() );
        putenv('WPSELENIUM_DRIVER=' . $this->wpSeleniumConfig->GetBroswerDriver() );
        putenv('WPSELENIUM_TEST_PORT=' . $this->wpSeleniumConfig->GetSeleniumRunPort() );
        
        //adding sleep command to give time for the command to full run/programs exceute
        sleep(1);
        # 3. Starting Selenium
        Logger::INFO("---- Starting Selenium----- \n\n");

        if (Requests::SiteUp("http://localhost:{$this->wpSeleniumConfig->GetSeleniumRunPort()}")){ 
            Requests::Get("http://localhost:{$this->wpSeleniumConfig->GetSeleniumRunPort()}/extra/LifecycleServlet?action=shutdown");
            sleep(1);
        } 
        Utilities::StartProcessInBackground($this->wpSeleniumConfig->GetSeleniumRunCommand());
        sleep(1);
    }

    function StartWPSeleniumTests(){
        $phpunitConfig = $this->wpSeleniumConfig->GetPhpUnitConfig();
        $phpunitConfigPath = $this->wpSeleniumConfig->GetPhpUnitConfigPath();
        
        if (!file_exists($phpunitConfigPath) || (file_exists($phpunitConfigPath) && !$phpunitConfig["isSample"])){
            $doc = new \DOMDocument("1.0", "ISO-8859-15");
            $doc->formatOutput = TRUE;
            $doc->loadXML(($this->wpSeleniumConfig->GetPhpUnitConfig()["config"])->asXML());
            $doc->saveXML();
            $doc->save( $phpunitConfigPath);
        }
        
        Logger::INFO("---- Running WPSelenium Tests---- \n\n");
        system($this->wpSeleniumConfig->GetPhpUnitPath());
        Logger::INFO("---- Completed Running WPSelenium Tests. Shutting Down.---- \n\n");
        sleep(1);
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
                    exec("taskkill /im chromedriver.exe /f");
                }
                else if ($this->wpSeleniumConfig->GetBroswerDriver() == CONSTS::SUPPORTED_DRIVERS_FIREFOX){
                }
                break;
        }
    }
    
    
}