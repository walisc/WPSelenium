<?php


namespace WPSelenium\Helpers;

interface HelperInterface{
    function GetProvisionClasses();
    function PhpUnitRunner($phpUnitPath);
    function SetEnvVariables();
    function SetConfig($WPSeleniumConfig);
}