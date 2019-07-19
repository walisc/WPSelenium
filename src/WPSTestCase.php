<?php

namespace WPSelenium;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverExpectedCondition;
use \PHPUnit\Framework\TestCase;
use Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\Remote\DesiredCapabilities;


abstract class WPSTestCase extends TestCase{

    
    private static $seleniumDriver;
    protected $driver;

    public function setUp() {
        $fp = fopen(sprintf('%s%s%s', WPSeleniumConfig::GetTempDirectory(), DIRECTORY_SEPARATOR, 'wp_selenium_current_test_file'), 'w');
        fwrite($fp,sprintf('%s;%s;%s' , (new \ReflectionClass(get_class($this)))->getFileName(), get_class($this), $this->getName()));
        fclose($fp);
        $this->driver = $this->GetSeleniumDriver();

    }

    protected function GetSeleniumDriver(){
        if (self::$seleniumDriver == null){
            $hosturl = sprintf("http://localhost:%s/wd/hub", getenv('WPSELENIUM_TEST_PORT'));
            self::$seleniumDriver = RemoteWebDriver::create($hosturl, DesiredCapabilities::{getenv('WPSELENIUM_DRIVER')}());    
            }
        return self::$seleniumDriver;
    }

    protected function GetWebPage($url){
        $this->driver->Get($url);
        $this->waitForPageToLoad();
    }

    protected function waitForPageToLoad(){
        $driver = $this->GetSeleniumDriver();
        $driver->wait()->until(
            function () use ($driver) {return $driver->executeScript('return document.readyState') == 'complete';}
        );
    }

    protected function waitForPageToLoadBasedOnElements($bys){
        $driver = $this->GetSeleniumDriver();
        $driver->wait()->until(
            function () use ($driver, $bys){
                $foundAll = true;
                foreach ($bys as $by){
                    if ($driver->findElement($by) != true)
                    {
                        $foundAll = false;
                        break;
                    }
                }
                return $foundAll;
            }

        );
    }

    protected function findElementWithWait($elementPath, $parent=null,  $timeoutInSecond = 30, $intervalInMillisecond = 250){
        $driver = $this->driver;

        $this->driver->wait($timeoutInSecond, $intervalInMillisecond)->until(
            function () use ($driver, $elementPath, $parent){
                try{
                    return  $parent != null ?  $parent->findElement($elementPath) : $driver->findElement($elementPath);

                }
                catch (NoSuchElementException $e){
                    return null;
                }
            }
        );
        return  $parent != null ?  $parent->findElement($elementPath) : $driver->findElement($elementPath);
    }

    protected function loginToWPAdmin(){
        //TODO: Check if wpsite first
        $this->driver->Get(sprintf('%s/wp-admin', $this->GetTestSite()));
        $this->waitForPageToLoad();
        if(strpos($this->driver->getCurrentURL(), 'wp-login')) {

            $usernameField = $this->driver->findElement(WebDriverby::id('user_login'));
            $passwordField = $this->driver->findElement(WebDriverby::id('user_pass'));
            $loginButton = $this->driver->findElement(WebDriverby::id('wp-submit'));

            $usernameField->click();
            $this->driver->getKeyboard()->sendKeys(getenv('WPSELENIUM_WP_TEST_USERNAME'));
            $passwordField->click();
            $this->driver->getKeyboard()->sendKeys(getenv('WPSELENIUM_WP_TEST_PASSWORD'));
            $loginButton->click();

            $this->waitForPageToLoad();
        }
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

