<?php

namespace WPSelenium;
use WPSelenium\Utilities\Logger;
use WPSelenium\Utilities\Requests;

class TestRunner{

    private $seleniumServerPath;
    private $binPath; 
    private $testSite;

    public static function RunTests(){
        (new self());
    }

    function __construct()
    {
        $wpSeleniumConfig = WPSeleniumConfig::Get();
        $this->seleniumServerPath = $wpSeleniumConfig->GetSeleniumServerPath();
        $this->binPath = $wpSeleniumConfig->GetBinDirectory();
        $this->testSite = $wpSeleniumConfig->GetSiteURL();

        $this->StartSeleniumServer();
    }

    function StartSeleniumServer(){
    
        Logger::INFO("Setting Enviroment variables");
        putenv('PATH=' . getenv('PATH') . PATH_SEPARATOR . $this->binPath);
        putenv('WPOOW_TEST_SITE=' . $this->testSite );
        //adding sleep command to give time for the command to full run/programs exceute
        sleep(1);
        # 3. Starting Selenium
        Logger::INFO("---- Starting Selenium----- \n\n");
    
        $fp = fsockopen("localhost",4444, $errno, $errstr,1);
        if($errstr == "" || $errno == 0){   
            Requests::Get(sprintf("http://localhost:4444/extra/LifecycleServlet?action=shutdown"));
            sleep(1);
        } 
        exec(sprintf("java -jar %s -role node -servlet org.openqa.grid.web.servlet.LifecycleServlet -registerCycle 0 -port 4444  > /dev/null 2>&1 &", $this->seleniumServerPath));   
        sleep(1);
        fclose($fp);
    }
    
    
}