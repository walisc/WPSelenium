<?php

namespace WPSelenium;

use \PHPUnit\Framework\TestCase;
use \PHPUnit\Util\Test;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\Remote\DesiredCapabilities;
use WPSelenium\Utilities\CONSTS;
use PHPUnit\Framework\Constraint\Exception;

abstract class WPSTestCase extends TestCase{

    
    private static $seleniumDriver;

    public static function setUpWPSite(){
        $Annotations = Test::parseTestMethodAnnotations(get_called_class(), getenv("CURRENT_WPSELENIUM_TEST"));
        if (array_key_exists(CONSTS::ANNOTATION_WP_BEFRE_RUN, $Annotations)){
            self::{$Annotations[CONSTS::ANNOTATION_WP_BEFRE_RUN]}();
        }
    }

    public function setUp() {
        putenv("CURRENT_WPSELENIUM_TEST=".$this->getName());
    }

    protected function GetSeleniumDriver(){
        if (self::$seleniumDriver == null){
            $hosturl = sprintf("http://localhost:%s/wd/hub", getenv('WPSELENIUM_TEST_PORT'));
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

