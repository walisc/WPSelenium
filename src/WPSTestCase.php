<?php

namespace WPSelenium;

use \PHPUnit\Framework\TestCase;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\Remote\DesiredCapabilities;

abstract class WPSTestCase extends TestCase{

    function setUpWPSite(){}

    private static $seleniumDriver;

    protected function GetSeleniumDriver(){
        if (self::$seleniumDriver == null){
            $hosturl = sprintf("http://localhost:%s/wd/hub", getenv('WPSELENIUM_TEST_PORT'));
            //Note: Report bug. Unable to catch exception when this fails
            self::$seleniumDriver = RemoteWebDriver::create($hosturl, DesiredCapabilities::{getenv('WPSELENIUM_DRIVER')}());    
            }
        return self::$seleniumDriver;
    }

    public function GetTestSite(){
        return getenv('WPSELENIUM_TEST_SITE');
    }

    public function GetSelectedBrowser(){
        return getenv('WPSELENIUM_DRIVER');
    }

    function __destruct()
    {
        if (self::$seleniumDriver != null){
        self::$seleniumDriver->quit();
        }
    }
}

