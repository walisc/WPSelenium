<?php

namespace WPSelenium;

use Facebook\WebDriver\WebDriverBy;
use \PHPUnit\Framework\TestCase;
use \PHPUnit\Util\Test;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\Remote\DesiredCapabilities;
use WPSelenium\Utilities\CONSTS;
use PHPUnit\Framework\Constraint\Exception;
use WPSelenium\WPSeleniumConfig;

abstract class WPSTestCase extends TestCase{

    
    private static $seleniumDriver;

    public static function setUpWPSite(){
        $currentTestPath = sprintf('%s%s%s', WPSeleniumConfig::GetTempDirectory(), DIRECTORY_SEPARATOR, 'wp_selenium_current_test_file');
        if (file_exists($currentTestPath))
        {
            $myfile = fopen($currentTestPath, 'r');
            $current_wp_selenium_test =  fgets($myfile);
            fclose($myfile);

            $MethodAnnotations = Test::parseTestMethodAnnotations(get_called_class(), $current_wp_selenium_test)['method'];
            if (array_key_exists(CONSTS::ANNOTATION_WP_BEFORE_RUN, $MethodAnnotations)){
                if (method_exists(get_called_class(),$MethodAnnotations[CONSTS::ANNOTATION_WP_BEFORE_RUN][0] )){
                    get_called_class()::{$MethodAnnotations[CONSTS::ANNOTATION_WP_BEFORE_RUN][0]}();
                }
                
            }
        }
    }

    public function setUp() {
        $fp = fopen(sprintf('%s%s%s', WPSeleniumConfig::GetTempDirectory(), DIRECTORY_SEPARATOR, 'wp_selenium_current_test_file'), 'w');
        fwrite($fp,$this->getName());
        fclose($fp);
    }

    protected function GetSeleniumDriver(){
        if (self::$seleniumDriver == null){
            $hosturl = sprintf("http://localhost:%s/wd/hub", getenv('WPSELENIUM_TEST_PORT'));
            self::$seleniumDriver = RemoteWebDriver::create($hosturl, DesiredCapabilities::{getenv('WPSELENIUM_DRIVER')}());    
            }
        return self::$seleniumDriver;
    }

    protected function loginToWPAdmin(){
        $driver = $this->GetSeleniumDriver();
        $driver->Get(sprintf('%s/wp-admin', $this->GetTestSite()));

        $usernameField = $driver->findElement(WebDriverby::id('user_login'));
        $passwordField = $driver->findElement(WebDriverby::id('user_pass'));
        $loginButton = $driver->findElement(WebDriverby::id('wp-submit'));


        $usernameField->click();
        $driver->getKeyboard()->sendKeys(getenv('WPSELENIUM_WP_TEST_USERNAME'));
        $passwordField->click();
        $driver->getKeyboard()->sendKeys(getenv('WPSELENIUM_WP_TEST_PASSWORD'));
        $loginButton->click();
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

