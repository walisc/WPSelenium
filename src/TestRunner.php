<?php

namespace WPSelenium;
use WPSelenium\Utilities\Logger;
use WPSelenium\Utilities\Requests;
use WPSelenium\Utilities\CONSTS;

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
    
        $fp = fsockopen("localhost",$this->wpSeleniumConfig->GetSeleniumRunPort(), $errno, $errstr,1);
        if($errstr == "" || $errno == 0){   
            Requests::Get("http://localhost:{$this->wpSeleniumConfig->GetSeleniumRunPort()}/extra/LifecycleServlet?action=shutdown", );
            sleep(1);
        } 
        exec($this->wpSeleniumConfig->GetSeleniumRunCommand());   
        sleep(1);
        fclose($fp);
    }

    function StartWPSeleniumTests(){
        $doc = new \DOMDocument("1.0", "ISO-8859-15");
        $doc->formatOutput = TRUE;
        $doc->loadXML($this->wpSeleniumConfig->GetPhpUnitConfig()->asXML());
        $doc->saveXML();
        $doc->save( $this->wpSeleniumConfig->GetPhpUnitConfigPath());

        Logger::INFO("---- Running WPSelenium Tests---- \n\n");
        system($this->wpSeleniumConfig->GetPhpUnitPath());
        Requests::Get("http://localhost:{$this->wpSeleniumConfig->GetSeleniumRunPort()}/extra/LifecycleServlet?action=shutdown");
        sleep(1);
    }
    
    
}