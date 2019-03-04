<?php

namespace WPSelenium;

use \PHPUnit\Framework\TestCase;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class WPSTestCase extends TestCase{

    abstract function BeforeRun();

    private static $seleniumDriver;

    protected function GetSeleniumDriver(){
        if (self::$seleniumDriver == null){
            $hosturl = "http://localhost:4444/wd/hub";
            //TODO: Report bug. Unable to catch exception when this fails
            switch($this->GetSelectedBrowser()){
                case 'chrome':
                    self::$seleniumDriver = RemoteWebDriver::create($hosturl, DesiredCapabilities::chrome());
                    break;
                case 'firefox':
                    self::$seleniumDriver = RemoteWebDriver::create($hosturl, DesiredCapabilities::firefox());
                    break;
                    
            }
        }
        return self::$seleniumDriver;
    }

    public function GetTestSite(){
        return sprintf("http://%s/", getenv('WPSELENIUM_TEST_SITE'));
    }

    public function GetSelectedBrowser(){
        return getenv('WPSELENIUM_DRIVER');
    }
}

