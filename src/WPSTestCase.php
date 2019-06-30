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


    public function setUp() {
        $fp = fopen(sprintf('%s%s%s', WPSeleniumConfig::GetTempDirectory(), DIRECTORY_SEPARATOR, 'wp_selenium_current_test_file'), 'w');
        fwrite($fp,sprintf('%s;%s;%s' , (new \ReflectionClass(get_class($this)))->getFileName(), get_class($this), $this->getName()));
        fclose($fp);
    }

    protected function GetSeleniumDriver(){
        if (self::$seleniumDriver == null){
            $hosturl = sprintf("http://localhost:%s/wd/hub", getenv('WPSELENIUM_TEST_PORT'));
            self::$seleniumDriver = RemoteWebDriver::create($hosturl, DesiredCapabilities::{getenv('WPSELENIUM_DRIVER')}());    
            }
        return self::$seleniumDriver;
    }

    protected function getWebPage($url){
        $driver = $this->GetSeleniumDriver();
        $driver->Get($url);
        $this->waitForPageToLoad();
    }

    protected function waitForPageToLoad(){
        $driver = $this->GetSeleniumDriver();
        $driver->wait()->until(
            $driver->executeScript('return document.readyState') == 'complete'
        );
    }

    protected function loginToWPAdmin(){
        //TODO: Check if wpsite first
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

        $this->waitForPageToLoad();
    }

    public function GetTestSite(){
        return getenv('WPSELENIUM_TEST_SITE');
    }

    public function GetSelectedBrowser(){
        return getenv('WPSELENIUM_DRIVER');
    }

    static function tearDownAfterClass()
    {
        if (self::$seleniumDriver != null){
        self::$seleniumDriver->quit();
        }
    }
}

