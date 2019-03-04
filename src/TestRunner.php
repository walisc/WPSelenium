<?php

namespace WPSelenium;
use WPSelenium\Utilities\Logger;
use WPSelenium\Utilities\Requests;
use WPSelenium\Utilities\CONSTS;

class TestRunner{

    private $seleniumServerPath;
    private $binPath; 
    private $testSite;
    private $phpUnitPath;
    private $phpUnitConfig;
    private $phpUnitConfigPath;
    private $selectedBrowserDriver;
    private $testDirectories;

    public static function RunTests(){
        (new self());
    }

    function __construct()
    {
        $wpSeleniumConfig = WPSeleniumConfig::Get();
        $this->seleniumServerPath = $wpSeleniumConfig->GetSeleniumServerPath();
        $this->binPath = $wpSeleniumConfig->GetBinDirectory();
        $this->testSite = $wpSeleniumConfig->GetSiteURL();
        $this->phpUnitPath = $wpSeleniumConfig->GetPhpUnitPath();
        $this->phpUnitConfig = $wpSeleniumConfig->GetPhpUnitConfig();
        $this->phpUnitConfigPath = $wpSeleniumConfig->GetPhpUnitConfigPath();
        $this->selectedBrowserDriver = $wpSeleniumConfig->GetBroswerDriver();
        $this->testDirectories =  $wpSeleniumConfig->GetTestFiles();
        $fp = fopen(sprintf("%s%s%s", __DIR__, DIRECTORY_SEPARATOR, CONSTS::WPSELENIUM_TEMP_TEST_FILE), 'w');
        fwrite($fp,json_encode( $this->testDirectories));
        fclose($fp);
        $this->StartSeleniumServer();
        $this->StartWPSeleniumTests();
    }

    function StartSeleniumServer(){
    
        Logger::INFO("Setting Enviroment variables");
        putenv('PATH=' . getenv('PATH') . PATH_SEPARATOR . $this->binPath);
        putenv('WPSELENIUM_TEST_SITE=' . $this->testSite );
        putenv('WPSELENIUM_DRIVER=' . $this->selectedBrowserDriver );
        
        //adding sleep command to give time for the command to full run/programs exceute
        sleep(1);
        # 3. Starting Selenium
        Logger::INFO("---- Starting Selenium----- \n\n");
    
        $fp = fsockopen("localhost",4444, $errno, $errstr,1);
        if($errstr == "" || $errno == 0){   
            Requests::Get("http://localhost:4444/extra/LifecycleServlet?action=shutdown");
            sleep(1);
        } 
        exec(sprintf("java -jar %s -role node -servlet org.openqa.grid.web.servlet.LifecycleServlet -registerCycle 0 -port 4444  > /dev/null 2>&1 &", $this->seleniumServerPath));   
        sleep(1);
        fclose($fp);
    }

    function StartWPSeleniumTests(){
        $doc = new \DOMDocument("1.0", "ISO-8859-15");
        $doc->formatOutput = TRUE;
        $doc->loadXML($this->phpUnitConfig->asXML());
        $doc->saveXML();
        $doc->save($this->phpUnitConfigPath);

        Logger::INFO("---- Running WPSelenium Tests---- \n\n");
        system($this->phpUnitPath);
        Requests::Get("http://localhost:4444/extra/LifecycleServlet?action=shutdown");
        sleep(1);
    }
    
    
}