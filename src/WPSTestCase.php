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
        $this->driver = $this->GetSeleniumDriver();;

    }

    public function GetSeleniumDriver(){
        if (self::$seleniumDriver == null){
            $hosturl = sprintf("http://localhost:%s/wd/hub", getenv('WPSELENIUM_TEST_PORT'));
            self::$seleniumDriver = RemoteWebDriver::create($hosturl, DesiredCapabilities::{getenv('WPSELENIUM_DRIVER')}());    
            }
        return self::$seleniumDriver;
    }

    public function GetWebPage($url){
        $this->driver->Get($url);
        $this->waitForPageToLoad();
    }

    public function waitForPageToLoad(){
        $driver = $this->GetSeleniumDriver();
        $driver->wait()->until(
            function () use ($driver) {return $driver->executeScript('return document.readyState') == 'complete';}
        );
    }

    public function waitForPageToLoadBasedOnElements($bys){
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

    public function findElementWithWait($elementPath, $parent=null, $shouldBeInteractable=true, $timeoutInSecond = 30, $intervalInMillisecond = 250){
        $driver = $this->driver;

        $this->driver->wait($timeoutInSecond, $intervalInMillisecond)->until(
            function () use ($driver, $elementPath, $parent, $shouldBeInteractable){
                try{
                    $element =  $parent != null ?  $parent->findElement($elementPath) : $driver->findElement($elementPath);
                    if (!$shouldBeInteractable){
                        return $element;
                    }
                    $this->driver->executeScript("arguments[0].scrollIntoView(false)", [$element]);
                    return $element->isDisplayed() ? $element : null;

                }
                catch (NoSuchElementException $e){
                    return null;
                }
            }
        );
        return  $parent != null ?  $parent->findElement($elementPath) : $driver->findElement($elementPath);
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

