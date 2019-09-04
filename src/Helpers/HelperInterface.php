<?php


namespace WPSelenium\Helpers;

use WPSelenium\Utilities\PhpUnit\Runner;

interface HelperInterface{
    function GetProvisionClasses();
    function PhpRunner(Runner $PhpUnitRunner);
    function SetConfig($WPSeleniumConfig);
}